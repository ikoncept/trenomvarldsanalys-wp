
@if($showFilter)
    <div class="s-archive-filter s-archive-filter--{{ $filterPosition }} {{ $filterPosition == 'top' ? 'u-margin__top--4' : '' }}">
        <div class="o-container">
            @form([
                'method' => 'GET',
                'action' => '?q=form_component'
            ])

                <div class="o-grid">
                    @if($enableTextSearch) 
                        <div class="o-grid-auto">
                            @field(
                                [
                                    'type' => 'text',
                                    'value' => $queryParameters->search,
                                    'placeholder' => $lang->searchFor,
                                    'classList' => ['u-width--100'],
                                    'attributeList' => [
                                        'type' => 'text',
                                        'name' => 's'
                                    ],
                                    'required' => false,
                                ]
                            )
                            @endfield
                        </div>
                    @endif

                    @if($enableDateFilter && $enableTextSearch) 
                        <div class="o-grid-fit">
                            @button([
                                'icon' => 'date_range',
                                'toggle' => true,
                                'attributeList' => ['js-toggle-trigger' => 'dateWrapper'],
                                'style' => 'basic',
                            ])
                            @endbutton
                        </div>
                    @endif
                
                </div>
            
                @if($enableDateFilter) 
                    <div class="{{ $showDatePickers ? '' : 'u-display--none' }}" js-toggle-item="dateWrapper" js-toggle-class="u-display--none">
                        <div class="o-grid">
                            <div class="o-grid-12 o-grid-auto@sm">
                                @field([
                                    'type' => 'datepicker',
                                    'value' => $queryParameters->from,
                                    'label' => $lang->fromDate,
                                    'attributeList' => [
                                        'type' => 'text',
                                        'name' => 'from',
                                        'data-invalid-message' => $lang->dateInvalid,
                                        'js-archive-filter-from'
                                    ],
                                    'required' => false,
                                    'datepicker' => [
                                        'title'                 => $lang->fromDate,
                                        'minDate'               => false,
                                        'maxDate'               => false,
                                        'required'              => true,
                                        'showResetButton'       => true,
                                        'showDaysOutOfMonth'    => true,
                                        'showClearButton'       => true,
                                        'hideOnBlur'            => true,
                                        'hideOnSelect'          => false,
                                    ]
                                ])
                                @endfield
                            </div>
                            <div class="o-grid-12 o-grid-auto@sm">
                                @field([
                                    'type' => 'datepicker',
                                    'value' => $queryParameters->to,
                                    'label' => $lang->toDate,
                                    'attributeList' => [
                                        'type' => 'text',
                                        'name' => 'to',
                                        'data-invalid-message' => $lang->dateInvalid,
                                        'js-archive-filter-to' => ''
                                    ],
                                    'required' => false,
                                    'datepicker' => [
                                        'title'                 => $lang->toDate,
                                        'minDate'               => false,
                                        'maxDate'               => false,
                                        'required'              => true,
                                        'showResetButton'       => true,
                                        'showDaysOutOfMonth'    => true,
                                        'showClearButton'       => true,
                                        'hideOnBlur'            => true,
                                        'hideOnSelect'          => false,
                                        ]
                                    ]
                                )
                                @endfield
                            </div>
                        </div>
                    </div>
                @endif  

                {{-- Select dropdowns for filtering --}}
                <div class="o-grid">
                    @foreach($taxonomyFilters as $key => $select)
                        <div class="o-grid-12 o-grid-auto@sm">
                            @select($select)
                            @endselect
                        </div>
                    @endforeach

                    @if($enableDateFilter && !$enableTextSearch) 
                        <div class="o-grid-fit">
                            @button([
                                'icon' => 'date_range',
                                'toggle' => true,
                                'attributeList' => ['js-toggle-trigger' => 'dateWrapper'],
                                'style' => 'basic',
                            ])
                            @endbutton
                        </div>
                    @endif
                </div>

                <div class="o-grid">
                    <div class="o-grid-auto">
                        @button([
                            'text' => $lang->searchBtn,
                            'color' => 'primary',
                            'type' => 'basic'
                        ])
                        @endbutton

                        @if($showFilterReset && $archiveBaseUrl) 
                            @button([
                                'href' => $archiveBaseUrl,
                                'text' => $lang->resetBtn,
                                'type' => 'basic'
                            ])
                            @endbutton
                        @endif
                    </div>
                </div>
            @endform
        </div>
    </div>
@endif