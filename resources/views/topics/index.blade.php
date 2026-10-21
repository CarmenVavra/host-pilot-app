@extends('layouts.app')

@section('title', 'Themen · '.$event->title)

@section('content')
    <x-event-header :event="$event" active="themen" />

    @php($topics = $event->topics)

    <div class="split">
        {{-- Topic list --}}
        <section class="split__main {{ $formOpen ? 'only-desktop' : '' }}" aria-labelledby="topics-title">
            <div class="section-head section-head--always">
                <h2 id="topics-title">Themen zum Besprechen</h2>
                @if ($topics->isNotEmpty())
                    <span class="hint">{{ $topics->count() === 1 ? '1 Thema' : $topics->count().' Themen' }}</span>
                @endif
            </div>

            @if ($topics->isEmpty())
                <div class="empty">
                    <div class="empty__title">Noch keine Themen</div>
                    <p>Halte fest, was du mit deinen Gästen besprechen möchtest – mit Notizen zu jedem Thema.</p>
                    <a class="btn only-mobile" href="{{ route('events.topics.create', $event) }}"><x-icon name="plus" :size="18" :stroke="2.5" />Thema hinzufügen</a>
                </div>
            @else
                <ol class="topic-list">
                    @foreach ($topics as $topic)
                        <li class="topic-card {{ $editing && $editing->id === $topic->id ? 'is-editing' : '' }}">
                            <span class="topic-card__number" aria-hidden="true">{{ $loop->iteration }}</span>
                            <div class="topic-card__body">
                                <h3 class="topic-card__title">{{ $topic->title }}</h3>
                                @if (filled($topic->notes))
                                    <p class="topic-card__notes">{{ $topic->notes }}</p>
                                @endif
                            </div>
                            <div class="topic-card__tools">
                                <a class="icon-btn" href="{{ route('events.topics.edit', [$event, $topic]) }}" aria-label="Thema «{{ $topic->title }}» bearbeiten" title="Bearbeiten">
                                    <x-icon name="pencil" :size="18" />
                                </a>
                                <form method="POST" action="{{ route('events.topics.destroy', [$event, $topic]) }}" data-confirm="Thema «{{ $topic->title }}» löschen?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn icon-btn--danger" aria-label="Thema «{{ $topic->title }}» löschen" title="Löschen">
                                        <x-icon name="trash" :size="18" />
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        {{-- New / edit panel: side panel on the web, own screen on the phone --}}
        <aside class="split__side split__side--380 {{ $formOpen ? '' : 'only-desktop' }}" aria-labelledby="topic-panel-title">
            <div class="panel">
                <h2 id="topic-panel-title">{{ $editing ? 'Thema bearbeiten' : 'Neues Thema' }}</h2>

                <form class="form" method="POST"
                      action="{{ $editing ? route('events.topics.update', [$event, $editing]) : route('events.topics.store', $event) }}">
                    @csrf
                    @if ($editing)
                        @method('PUT')
                    @endif

                    <x-input name="title" label="Überschrift" :value="$editing?->title" required maxlength="120" placeholder="z. B. Sommerferien gemeinsam planen" />

                    <div class="field">
                        <label class="field__label" for="f-notes">Notizen (optional)</label>
                        <textarea class="textarea @error('notes') is-invalid @enderror" id="f-notes" name="notes" maxlength="5000"
                                  placeholder="Stichworte, Fragen, Ideen …"
                                  @error('notes') aria-invalid="true" aria-describedby="f-notes-error" @enderror>{{ old('notes', $editing?->notes) }}</textarea>
                        @error('notes')<span class="error" id="f-notes-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn--block">{{ $editing ? 'Speichern' : 'Hinzufügen' }}</button>
                        @if ($formOpen)
                            <a class="btn btn--ghost btn--block" href="{{ route('events.topics.index', $event) }}">Abbrechen</a>
                        @endif
                    </div>
                </form>
            </div>
        </aside>
    </div>

    @unless ($formOpen)
        <x-fab :href="route('events.topics.create', $event)" label="Thema hinzufügen" />
    @endunless
@endsection
