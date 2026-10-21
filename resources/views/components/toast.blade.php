@if (session('status'))
    <div class="toast" role="status" aria-live="polite" data-toast>
        <span>{{ session('status') }}</span>
        <button type="button" class="toast__close" data-toast-close aria-label="Meldung schliessen">
            <x-icon name="x" :size="16" :stroke="2.5" />
        </button>
    </div>
@endif
