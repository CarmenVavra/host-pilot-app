{{--
    Shared confirmation dialog for destructive actions.
    Opened by resources/js/app.js for every <form data-confirm="…">; optional
    data-confirm-button sets the label of the confirm button (default «Löschen»).
--}}
<dialog class="confirm" id="confirm-dialog" aria-labelledby="confirm-title" aria-describedby="confirm-text" data-confirm-dialog>
    <form method="dialog" class="confirm__inner">
        <div class="confirm__head">
            <span class="confirm__icon" aria-hidden="true"><x-icon name="trash" :size="22" /></span>
            <div>
                <div class="kicker confirm__kicker">Bitte bestätigen</div>
                <h2 class="confirm__title" id="confirm-title" data-confirm-message>Wirklich löschen?</h2>
            </div>
        </div>
        <p class="confirm__text" id="confirm-text">Das kann nicht rückgängig gemacht werden.</p>
        <div class="confirm__actions">
            <button type="submit" value="cancel" class="btn btn--ghost" autofocus>Abbrechen</button>
            <button type="submit" value="confirm" class="btn btn--danger" data-confirm-accept>Löschen</button>
        </div>
    </form>
</dialog>
