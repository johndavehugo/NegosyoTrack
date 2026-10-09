{{-- Shared control styles for the Economic Map module (no theme rebuild needed). --}}
<style>
    .emap-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19.5 8.25-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.625rem center;
        background-size: 1rem 1rem;
        padding-right: 2.25rem;
        cursor: pointer;
    }
    .emap-select:hover {
        border-color: #9ca3af;
    }
    .emap-input:focus, .emap-select:focus {
        outline: none;
    }
    .emap-results mark {
        background: #fef08a;
        color: inherit;
        border-radius: 0.25rem;
        padding: 0 0.125rem;
    }
    .dark .emap-results mark {
        background: rgba(250, 204, 21, 0.25);
    }
</style>
