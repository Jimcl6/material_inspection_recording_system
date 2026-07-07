import { ref } from 'vue';

type RowId = number | string;

export function useSingleExpandedRow() {
    const expandedRowId = ref<RowId | null>(null);

    const toggleExpanded = (id: RowId): void => {
        expandedRowId.value = expandedRowId.value === id ? null : id;
    };

    const isExpanded = (id: RowId): boolean => expandedRowId.value === id;

    return {
        expandedRowId,
        toggleExpanded,
        isExpanded,
    };
}
