export interface AnnealingCheck {
    id: number;
    item_code: string;
    receiving_date: string;
    supplier_lot_number: string;
    quantity: number;
    annealing_date: string;
    machine_number: string;
    created_at: string;
    updated_at: string;
}

export interface Filters {
    search?: string;
    [key: string]: any;
}

export interface AnnealingChecksResponse {
    data: AnnealingCheck[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    [key: string]: any;
}

export interface Props {
    annealingChecks: AnnealingChecksResponse;
    filters: Filters;
}
