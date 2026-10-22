@unless ($legal->isComplete())
    <div class="alert" role="alert">
        <strong>Platzhalter ausfüllen:</strong>
        Es fehlen noch Pflichtangaben ({{ implode(', ', array_map(fn ($key) => 'LEGAL_'.strtoupper($key), $legal->missing())) }}).
        Trage sie in der <code>.env</code> ein, bevor die Seite online geht, und lasse den Text bei Bedarf rechtlich prüfen.
    </div>
@endunless
