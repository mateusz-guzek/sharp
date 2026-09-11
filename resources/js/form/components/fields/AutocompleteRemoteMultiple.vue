<script setup lang="ts">
    import FormFieldLayout from "@/form/components/FormFieldLayout.vue";
    import { FormFieldEmits, FormFieldProps } from "@/form/types";
    import { FormAutocompleteItemData, FormAutocompleteRemoteFieldData } from "@/types";
    import { computed, ref } from "vue";
    import { ChevronsUpDown, X } from "lucide-vue-next";
    import { Badge } from "@/components/ui/badge";
    import {
        Command,
        CommandEmpty,
        CommandGroup,
        CommandInput,
        CommandItem,
        CommandList,
    } from "@/components/ui/command";
    import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
    import { __, trans_choice } from "@/utils/i18n";
    import { api } from "@/api/api";
    import { route } from "@/utils/url";
    import { useRemoteAutocomplete } from "@/composables/useRemoteAutocomplete";
    import { useParentForm } from "@/form/useParentForm";
    import { useFieldContainerData } from "@/form/useFieldContainerData";
    import { useParentListField } from "@/form/components/fields/list/useParentListField";
    import { useIsInDialog } from "@/components/ui/dialog/Dialog.vue";

    const props = defineProps<FormFieldProps<FormAutocompleteRemoteFieldData, FormAutocompleteItemData[]>>();
    const emit = defineEmits<FormFieldEmits<FormAutocompleteRemoteFieldData>>();
    const form = useParentForm();

    const open = ref(false);
    const searchTerm = ref('');
    const isInDialog = useIsInDialog();
    const parentListField = useParentListField();
    const fieldContainerData = useFieldContainerData(form);
    const selectedValues = computed(() => Array.isArray(props.value) ? props.value : []);

    const { results, loading, search } = useRemoteAutocomplete<FormAutocompleteItemData[]>(
        ({ query, signal, onSuccess, onError }) => {
            const fieldKey = parentListField && parentListField.form === form
                ? `${parentListField.props.field.key}.${props.field.key}`
                : props.field.key;

            return api.post(
                route('code16.sharp.api.form.autocomplete.index', {
                    entityKey: form.entityKey,
                    autocompleteFieldKey: fieldKey,
                    endpoint: props.field.remoteEndpoint,
                    search: query,
                    ...fieldContainerData,
                }),
                {
                    formData: props.field.callbackLinkedFields
                        ? Object.fromEntries(
                            Object.entries(props.parentData).filter(([fieldKey]) =>
                                props.field.callbackLinkedFields.includes(fieldKey)
                            )
                        )
                        : null,
                },
                { signal },
            )
                .then(onSuccess, onError)
                .then(response => response.data.data);
        },
        {
            debounceDelay: props.field.debounceDelay,
            minLength: props.field.searchMinChars,
        },
    );

    const availableResults = computed(() => results.value.filter(result => !isSelected(result)));

    function itemId(item: FormAutocompleteItemData): string | number {
        return item[props.field.itemIdAttribute];
    }

    function itemHtml(item: FormAutocompleteItemData): string {
        return item._htmlResult ?? item._html ?? String(itemId(item));
    }

    function itemLabel(item: FormAutocompleteItemData): string {
        return String(item.label ?? itemId(item));
    }

    function isSelected(item: FormAutocompleteItemData): boolean {
        return selectedValues.value.some(selected => String(itemId(selected)) === String(itemId(item)));
    }

    function updateValue(value: FormAutocompleteItemData[]): void {
        emit('input', value);
    }

    function onSelect(item: FormAutocompleteItemData): void {
        if (!isSelected(item)) {
            updateValue([...selectedValues.value, item]);
        }
    }

    function onRemove(item: FormAutocompleteItemData): void {
        updateValue(selectedValues.value.filter(selected => String(itemId(selected)) !== String(itemId(item))));
    }

    function onSearchInput(query: string): void {
        if (!query.length && !searchTerm.value) {
            return;
        }

        searchTerm.value = query;
        search(query);
    }

    function onOpen(): void {
        if (!searchTerm.value && props.field.searchMinChars === 0) {
            search('', true);
        }
    }

    function onOpenChange(value: boolean): void {
        if (value && props.field.readOnly) {
            open.value = false;
            return;
        }

        if (value) {
            onOpen();
        }
    }
</script>

<template>
    <FormFieldLayout v-bind="props" field-group v-slot="{ ariaLabelledBy, ariaDescribedBy }">
        <Popover v-model:open="open" @update:open="onOpenChange">
            <PopoverTrigger as-child>
                <div
                    class="relative flex min-h-10 w-full cursor-pointer flex-wrap items-center gap-1 rounded-md border border-input bg-background px-3 py-2 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 aria-disabled:pointer-events-none aria-disabled:opacity-50"
                    role="combobox"
                    aria-autocomplete="none"
                    tabindex="0"
                    :aria-labelledby="ariaLabelledBy"
                    :aria-describedby="ariaDescribedBy"
                    :aria-disabled="props.field.readOnly"
                >
                    <span v-if="!selectedValues.length" class="text-muted-foreground">
                        {{ props.field.placeholder ?? __('sharp::form.autocomplete.placeholder') }}
                    </span>

                    <Badge
                        v-for="item in selectedValues"
                        :key="itemId(item)"
                        variant="secondary"
                        class="max-w-52 gap-1 rounded-sm px-1 font-normal"
                    >
                        <span class="truncate" v-html="itemHtml(item)"></span>
                        <button
                            type="button"
                            class="rounded-sm opacity-70 outline-none hover:opacity-100 focus-visible:ring-2 focus-visible:ring-ring"
                            :disabled="props.field.readOnly"
                            :aria-label="__('sharp::form.tags.tag_delete_button.aria_label', { option_label: itemLabel(item) })"
                            @mousedown.prevent
                            @click.stop="onRemove(item)"
                        >
                            <X class="size-3" />
                        </button>
                    </Badge>

                    <ChevronsUpDown class="absolute right-3 size-4 shrink-0 opacity-50 text-foreground" />
                </div>
            </PopoverTrigger>

            <PopoverContent
                class="w-(--reka-popover-trigger-width) min-w-[200px] p-0"
                align="start"
                :avoid-collisions="false"
            >
                <Command
                    :class="isInDialog ? 'max-h-(--reka-popper-available-height)' : ''"
                    :model-value="selectedValues"
                    :by="props.field.itemIdAttribute"
                    ignore-filter
                    multiple
                    highlight-on-hover
                    :reset-search-term-on-select="false"
                >
                    <CommandInput
                        :model-value="searchTerm"
                        :display-value="() => searchTerm"
                        :placeholder="
                            props.field.searchMinChars > 1
                                ? trans_choice('sharp::form.autocomplete.query_too_short', props.field.searchMinChars, { min_chars: props.field.searchMinChars })
                                : __('sharp::form.autocomplete.placeholder')
                        "
                        @update:model-value="onSearchInput"
                    />

                    <CommandList>
                        <template v-if="loading">
                            <div class="px-4 py-6 text-center text-sm">
                                {{ __('sharp::form.autocomplete.loading') }}
                            </div>
                        </template>
                        <template v-else-if="!availableResults.length && searchTerm.length < props.field.searchMinChars">
                            <div class="px-4 py-6 text-center text-sm">
                                {{ trans_choice('sharp::form.autocomplete.query_too_short', props.field.searchMinChars, { min_chars: props.field.searchMinChars }) }}
                            </div>
                        </template>
                        <template v-else>
                            <CommandEmpty>
                                {{ __('sharp::form.autocomplete.no_results_text') }}
                            </CommandEmpty>
                            <CommandGroup v-if="availableResults.length">
                                <CommandItem
                                    v-for="item in availableResults"
                                    :key="itemId(item)"
                                    :value="item"
                                    @select.prevent="onSelect(item)"
                                >
                                    <div class="min-w-0 flex-1" v-html="itemHtml(item)"></div>
                                </CommandItem>
                            </CommandGroup>
                        </template>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    </FormFieldLayout>
</template>
