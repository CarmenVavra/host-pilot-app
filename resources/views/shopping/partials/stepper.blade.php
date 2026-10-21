<form class="stepper" method="POST" action="{{ route('events.guests.update', $event) }}">
    @csrf
    @method('PATCH')
    <span class="stepper__label" id="guests-label-{{ $suffix }}">Gäste</span>
    <div class="stepper__control" role="group" aria-labelledby="guests-label-{{ $suffix }}">
        <button type="submit" name="guests" value="{{ max(1, $event->guests - 1) }}" class="stepper__btn"
                aria-label="Ein Gast weniger" @disabled($event->guests <= 1)>−</button>
        <output class="stepper__value" aria-live="polite">{{ $event->guests }}</output>
        <button type="submit" name="guests" value="{{ min(500, $event->guests + 1) }}" class="stepper__btn"
                aria-label="Ein Gast mehr" @disabled($event->guests >= 500)>+</button>
    </div>
</form>
