<style>
    .modal-card-content {
        display: grid;
        align-items: end;
        height: 100%;
    }

    .scrollable {
        overflow-y: auto;
        overflow-x: hidden;
        height: 600px;
    }

    .modal-card-general {
        width: 140px;
        height: 140px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .modal-card-general:hover {
        transform: scale(1.05);
    }

    .scrollable::-webkit-scrollbar {
        width: 12px;
    }

    .scrollable::-webkit-scrollbar-track {
        background-color: #dbdbdb;
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
        border-radius: 10px;
    }

    .scrollable::-webkit-scrollbar-thumb {
        -webkit-box-shadow: inset 0 0 6px #1e43a0;
        border-radius: 10px;
        background-color: #2a53bd;
    }

    @media only screen and (max-width: 495px) {
        .modal-card-general {
            width: 26vw;
            height: 26vw;
        }

        .modal-card-general span {
            display: none;
        }
    }
</style>

{{-- Call this to get a custom modal --}}
<div class="modal fade" id="{{ $modal_id }}" aria-labelledby="{{ $modal_id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- Use this if modal is very custom --}}
            @if (isset($rawModal))
                {!! $rawModal !!}
            @endif
            {{-- Set hasHeader to true if you want the modal to have a header --}}
            @if (isset($hasHeader))
                <div class="modal-header">
                    @if (!isset($rawHeader))
                        <h5 class="modal-title">{{ $modalTitle }}</h5>
                    @else
                        {!! $rawHeader !!}
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif
            {{-- Set hasBody to true to use the body --}}
            @if (isset($hasBody))
                <div class="modal-body">
                    {{-- Show a message at the end of the modal [message => 'message', classes => 'list of classes'] --}}
                    @if (isset($messages))
                        @foreach ($messages as $message)
                            <label class="{{ $message['classes'] }}">{{ $message['message'] }}</label><br />
                        @endforeach
                    @endif
                    {{-- Set hasForm to true to have a form --}}
                    @if (isset($inputs))
                        {{-- Send array with following values input => [label, type, id, placeholder (optional), 'optional' => true (optional)] --}}
                        @foreach ($inputs as $input)
                            @if (isset($input['label']))
                                <label>{{ $input['label'] }}</label>
                            @endif
                            @if (isset($input['optional']))
                                <br /><span class="text-muted" style="font-size: 15px">(optional)</span>
                            @endif
                            @if (isset($input['isTextarea']))
                                <textarea type="{{ $input['type'] }}" @if (isset($input['placeholder'])) placeholder="{{ $input['placeholder'] }}" @endif
                                    class="form-control mb-3" id="{{ $input['id'] }}" autocomplete="off"></textarea>
                            @else
                                <input type="{{ $input['type'] }}"
                                    @if (isset($input['placeholder'])) placeholder="{{ $input['placeholder'] }}" @endif
                                    class="form-control mb-3" id="{{ $input['id'] }}" autocomplete="off"
                                    @if (isset($input['restrictFile'])) accept="image/*" @endif
                                    @if (isset($input['step'])) step="{{ $input['step'] }}" @endif>
                            @endif
                        @endforeach
                    @endif
                    {{-- If there's a select select [Configs, Options] --}}
                    @if (isset($select))
                        <label class="mt-1">{{ $select['configs']['label'] }}</label>
                        <select id="{{ $select['configs']['id'] }}" class="form-select {{ $select['configs']['id'] }}"
                            @if (isset($select['configs']['multiple'])) multiple @endif>
                            <option value="0" class="text-muted" @if (!isset($select['configs']['disabled'])) disabled @endif
                                selected>
                                {{ $select['configs']['default'] }}
                            </option>
                            @foreach ($select['options'] as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    @endif
                    {{-- Selectable Cards --}}
                    @if (isset($cards))
                        <div class="modal-menu" id="modal_menu" style="display: inline-flex">
                            <div class="scrollable">
                                <div class="row" style="padding-left: 0px">
                                    @foreach ($cards as $card)
                                        <div class="{{ count($cards) == 2 ? 'col-6' : 'col-4' }} modal-card-selectable"
                                            id="modal-card-selectable{{ $card['id'] }}">
                                            <style>
                                                .modal-card{{ $card['id'] }} {
                                                    margin: 5px 0px;
                                                    background: linear-gradient(180deg, rgba(0, 0, 0, 0) 47.4%, #000000 100%), url("{{ $card['image'] }}");
                                                    background-size: cover;
                                                    background-position: center;
                                                    border-radius: 15px;
                                                    padding: 10px 10px;
                                                }
                                            </style>
                                            <input type="hidden" value="{{ $card['id'] }}">
                                            <div class="modal-card{{ $card['id'] }} modal-card-general">
                                                <div class="modal-card-content">
                                                    <span class="unselectable" style="color:white; font-weight:600">
                                                        <span>{{ $card['label'] }}</span>
                                                        <span style="float: right">{{ $card['price'] }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- for custom body --}}
                    @if (isset($rawBody))
                        {!! $rawBody !!}
                    @endif
                </div>
            @endif
            {{-- Footer --}}
            @if (isset($hasFooter))
                <div class="modal-footer">
                    @if (isset($rawFooter))
                        {!! $rawFooter !!}
                    @endif
                    @if (isset($footerMessage))
                        <span class="text-muted">{{ $footerMessage }}</span>
                    @endif
                    {{-- Buttons on footer send array has [b1 => [label => 'button label', id => 'idOfBtn', 'class' => 'btn btn-primary', 'dismiss' => true (optional)]] --}}
                    @if (isset($buttons))
                        @foreach ($buttons as $button)
                            <button id="{{ $button['id'] }}" class="{{ $button['class'] }}"
                                @if (isset($button['function'])) onclick="{{ $button['function'] }}()" @endif
                                @if (isset($button['dismiss'])) data-bs-dismiss="modal" @endif>{{ $button['label'] }}</button>
                        @endforeach
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
