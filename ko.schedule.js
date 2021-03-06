(function(window){
    ko.components.register('schedule', {
        viewModel: ViewModel(),
        template: `
            <div class="schedule-widget">
                <div class="schedule-widget__info">
                    <!-- ko foreach: getTimeBlocks -->
                        <div class="schedule-widget__infoblock" data-bind="css: {'schedule-widget__infoblock--firstofdate': $data.displayDate}">
                            <!-- ko if: $data.displayDate -->
                                <span class="schedule-widget__date" data-bind="text: $component.getDateStr($data.date)"></span>
                            <!-- /ko -->
                        </div>
                    <!-- /ko -->
                </div>
                <div class="schedule-widget__schedule">
                    <div class="schedule-widget__infoevents">
                        <!-- ko foreach: infoevents -->
                            <div class="schedule-widget__event schedule-widget__event--info" data-bind="style: {
                            top: $component.getEventTopOffset($data),
                            height: $component.getEventHeight($data)+'px'
                            }, attr: {
                            class: 'schedule-widget__event schedule-widget__event--info schedule-widget__event--category-'+$data.category().toLowerCase(),
                            id: $data.id && 'schedule-widget__event__'+$data.id
                            }">
                                <span data-bind="text: $data.label" class="schedule-widget__event__label"></span>
                                <span class="schedule-widget__event__time">
                                    <span data-bind="text: $data.start"></span> to <span data-bind="text: $data.end"></span>
                                </span>
                            </div>
                        <!-- /ko -->
                    </div>
                    <div class="schedule-widget__background">
                        <!-- ko foreach: getTimeBlocks -->
                            <div class="schedule-widget__timeblock" data-bind="css: {'schedule-widget__timeblock--firstofdate': $data.displayDate}, attr: {id: $data.id && 'schedule-widget__event__'+$data.id}">
                                <span data-bind="text: $data.label"></span>
                            </div>
                        <!-- /ko -->
                    </div>
                    <div class="schedule-widget__events">
                        <div class="schedule-widget__columns">
                            <!-- ko foreach: getColumns -->
                                <div class="schedule-widget__column" data-bind="
                                style: {width: $component.getColumnsWidth()},
                                attr: {id: $component.getColumnDomId($data)},
                                event: {dragover: $component.handleDragOver,dragenter: $component.handleDragEnter,drop: $component.handleDrop}">
                                    <div class="schedule-widget__columnheader">
                                        <span data-bind="text: $data.name"></span>
                                    </div>
                                    <!-- ko foreach: $component.events -->
                                            <div class="schedule-widget__event" draggable="true" data-bind="
                                            visible: isOnColumn($parent),
                                            style: {
                                            top: $component.getEventTopOffset($data),
                                            height: $component.getEventHeight($data)+'px',
                                            width: $data.width()+'%',
                                            left: $data.left()+'%'
                                            },
                                            attr: {
                                            class: 'schedule-widget__event schedule-widget__event--category-'+$data.category().toLowerCase()+($data.isDirty() ? ' schedule-widget__event--dirty': ''),
                                            id: $data.id && 'schedule-widget__event__'+$data.id,
                                            title: $data.label,
                                            'data-visible': isOnColumn($parent)
                                            },
                                            event: {
                                            contextmenu: $component.getContextMenuHandler($data),
                                            dragstart: $component.handleDragStart,
                                            dragend: $component.getDragEndHandler($component)
                                            }">
                                                <span class="schedule-widget__event__time" data-bind="attr: {title: $data.start() + ' - ' + $data.end}">
                                                    <span data-bind="text: $data.start"></span>
                                                    -
                                                    <!-- ko ifnot: $data.overridedEnd -->
                                                        <span data-bind="text: $data.end"></span>
                                                    <!-- /ko -->
                                                    <!-- ko if: $data.overridedEnd -->
                                                        <span data-bind="text: $data.overridedEnd"></span>
                                                    <!-- /ko -->
                                                </span>
                                                <span data-bind="text: $data.label" class="schedule-widget__event__label"></span>
                                                <div class="schedule-widget__event__actions schedule-widget__btn-group">
                                                    <!-- ko foreach: $component.getEventActions($data) -->
                                                        <!-- ko if: $data.isMain -->
                                                            <a href="" class="schedule-widget__btn schedule-widget__btn--default" data-bind="text: $data.label, click: $data.action">
                                                            </a>
                                                        <!-- /ko -->
                                                    <!-- /ko -->
                                                </div>
                                                <div class="schedule-widget__event__stateactions">
                                                    <a href="" class="schedule-widget__btn schedule-widget__btn--success" data-bind="click: $data.saveCurrentState"><i class="fa fa-save"></i></a>
                                                    <a href="" class="schedule-widget__btn schedule-widget__btn--warning" data-bind="click: $data.restorePrevState"><i class="fa fa-undo"></i></a>
                                                </div>
                                            </div>
                                    <!-- /ko -->
                                </div>
                            <!-- /ko -->
                        </div>

                        <!-- ko foreach: events -->
                                <div class="schedule-widget__event" data-bind="
                                visible: !$data.column,
                                style: {
                                top: $component.getEventTopOffset($data),
                                height: $component.getEventHeight($data)+'px',
                                width: $data.width()+'%',
                                left: $data.left()+'%'
                                },
                                attr: {
                                class: 'schedule-widget__event schedule-widget__event--category-'+$data.category().toLowerCase(),
                                id: $data.id && 'schedule-widget__event__'+$data.id,
                                title: $data.label
                                },
                                event: {
                                contextmenu: $component.getContextMenuHandler($data)
                                }">
                                    <span class="schedule-widget__event__time" data-bind="attr: {title: $data.start() + ' - ' + $data.end}">
                                        <span data-bind="text: $data.start"></span>
                                        -
                                        <!-- ko ifnot: $data.overridedEnd -->
                                            <span data-bind="text: $data.end"></span>
                                        <!-- /ko -->
                                        <!-- ko if: $data.overridedEnd -->
                                            <span data-bind="text: $data.overridedEnd"></span>
                                        <!-- /ko -->
                                    </span>
                                    <span data-bind="text: $data.label" class="schedule-widget__event__label"></span>
                                    <div class="schedule-widget__event__actions schedule-widget__btn-group">
                                        <!-- ko foreach: $component.getEventActions($data) -->
                                            <!-- ko if: $data.isMain -->
                                                <a href="" class="schedule-widget__btn schedule-widget__btn--default" data-bind="text: $data.label, click: $data.action">
                                                </a>
                                            <!-- /ko -->
                                        <!-- /ko -->
                                    </div>
                                </div>
                        <!-- /ko -->
                    </div>
                </div>
            </div>
        `
    });

    var contextualMenuDomElem = $('<ul class="contextual-menu">');
    var contextualMenuItemDomElem = $('<li class="contextual-menu__item">');
    var contextualMenuItemIconDomElem = $('<span class="contextual-menu__item__icon"><i class="fa">');

    $(function(){
        $(window).on('resize', function(){
            contextualMenuDomElem.hide();
        });
        $(window).on('scroll', function(){
            contextualMenuDomElem.hide();
        });
        $('html').on('click', function(){
            contextualMenuDomElem.hide();
        });
    });

    function pad(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }

    function timeToDecimal(time){
        var start = time.split(':');
        var sum = 0;
        for(var i = start.length - 1; i >= 0; i--){
            if(i === 3){
                sum = sum + (parseFloat(start[i] / 108000));
            }
            if(i === 2){
                sum = sum + (parseFloat(start[i] / 3600));
            }
            if(i === 1){
                sum = sum + (parseFloat(start[i] / 60));
            }
            if(i === 0){
                sum = sum + parseFloat(start[i]);
            }
        }
        return sum;
    }

    function decimalToTime(decimal){

        var hours = Math.floor(decimal % 60);
        var minutes = Math.floor(Math.floor((decimal * 60) % 60));
        var seconds = Math.floor(parseFloat(parseFloat((decimal * 3600) % 60)) % 60);
        var frames = Math.round(parseFloat(parseFloat((decimal * 108000) % 30)) % 30);

        // TODO: This pad mapping can be simplified by right currying the pad function with 2.
        return [hours, minutes, seconds, frames].map(function(n){return pad(n,2);}).join(':');
    }

    function getDateStr(date){
        return moment(date).format('MMM DD');
    }

    function locateIntoView(elem){
        var hide = false;
        if(!elem.is(':visible')){
            hide = true;
        }
        elem.show();
        var win = $(window);
        var elemOffset = elem.offset();
        var elemHeight = elem.height();
        var elemWidth = elem.width();
        var visibleToY = win.height() + win.scrollTop();
        var visibleToX = win.width() + win.scrollLeft();
        if(elemHeight + elemOffset.top > visibleToY){
            var top = elemOffset.top - elemHeight;
            elem.css({
                top: top > 0 ? top : 0 + 'px'
            });
        }
        if(elemWidth + elemOffset.left > visibleToX){
            var left = elemOffset.left - elemWidth;
            elem.css({
                left: left > 0 ? left : 0 + 'px'
            });
        }
        if(hide) elem.hide();
    }

    function TimeBlock(time){
        this.time = time;
        this.date;
        this.displayDate = false;
        this.id;
    }

    Object.defineProperty(TimeBlock.prototype, 'label', {
        get:function(){
            return decimalToTime(this.time);
        }
    });

    function Event(label, start, duration, date, category, column, onSave){
        var prevState = ko.observableArray([]);
        var self = this;
        this.label = label;
        this.category = category ? category : ko.observable('default');
        this.start = ko.observable(start ? decimalToTime(timeToDecimal(start())) : undefined);
        this.duration = duration;
        this.date = date;
        this.column = column;
        this.overlaps = ko.observableArray([]);
        this.id;
        this.item;
        this.overridedEnd = ko.observable();
        this.width = ko.observable(100);
        this.left = ko.observable(0);
        this.isOnColumn = function(column){
            return this.column && column.name == this.column();
        };
        this.takeStateSnapshot = function(){
            prevState.push(ko.toJS(this));
        };
        this.onSave = onSave;
        this.isDirty = ko.computed(function(){
            if(prevState() && prevState().length){
                if(self.column() != prevState()[0].column){
                    return true;
                }
            }
            return false;
        });
        this.restorePrevState = function(clean){
            var clean = (typeof clean != 'undefined') ? clean : false;
            self.column((clean ? prevState()[0] : prevState.pop()).column);
            if(clean){
                prevState([]);
            }
        };
        this.saveCurrentState = function(){
            self.onSave(self).then(function(r){
                if (r) {
                    prevState([]);
                }
            });
        };
    }

    function Column(name, id){
        this.id = id;
        this.name = name;
    }

    Object.defineProperty(Event.prototype, 'end', {
        get:function(){
            var endTime = decimalToTime(timeToDecimal(this.start()) + timeToDecimal(this.duration()));
            endTime = endTime.split(':');
            endTime = [parseInt(endTime.shift()) % 24].concat(endTime).join(':');
            return decimalToTime(timeToDecimal(endTime));
        }
    });

    function ViewModel() {

        var _columns;
        var _columnsMapping = {};
        var _draggingEvent;

        var defaults = {
            block: ko.observable('00:30:00'),
            start: ko.observable('00:00:00'),
            duration: ko.observable('24:00:00'),
            blockHeight: ko.observable(30),
            dayStartsAt: ko.observable('00:00:00'),
            startDate: ko.observable(new Date()),
            columns: ko.observable([]),
            onDropEventOnColumn: function(){},
            onSaveEvent: function(){
                var def = $.Deferred();
                def.resolve(true);
                return def;
            }
        };


        function refreshOverlaps(obs){
            var events = typeof obs == 'function' ? obs() : obs;
            if(events){
                events.map(function(ev){
                    ev.overlaps([]);
                    ev.left(0);
                    ev.width(100);
                });
                events.map(function(ev){
                    events.map(function(e){
                        if(
                            (e != ev && (!ev.overlaps || ev.overlaps.indexOf(e) < 0))
                            &&
                            ((!e.column && !ev.column) || (e.column && ev.column && e.column() === ev.column()))
                            &&
                            (
                                (timeToDecimal(e.end) > timeToDecimal(ev.start()) && timeToDecimal(e.end) < timeToDecimal(ev.end))
                                ||
                                (timeToDecimal(ev.end) > timeToDecimal(e.start()) && timeToDecimal(ev.end) < timeToDecimal(e.end))
                                ||
                                (ev.start() == e.start() && ev.end == e.end)
                            )
                          )
                        {
                            ev.overlaps.push(e);
                            e.overlaps.push(ev);
                        }
                    });
                });

                var ordered = events;
                ordered.sort(function(a, b){
                    return timeToDecimal(a.start()) - timeToDecimal(b.start());
                });

                ordered.map(function(e){
                    var w = (100 / (e.overlaps().length + 1));
                    e.width(w);
                    e.overlaps().map(function(o){
                        if(timeToDecimal(e.start()) <= timeToDecimal(o.start())){
                            if(e.left() == 0){
                                o.left(w);
                            }else{
                                o.left(e.overlaps().reduce(function(a, b){
                                    if(a > b.left()){
                                        if(o.overlaps().indexOf(b) < 0){
                                            return b.left();
                                        }else{
                                            return a;
                                        }
                                    }else{
                                        return a + b.left();
                                    }
                                }, w));
                            }
                        }
                    });
                    if(!e.overlaps().length){
                        e.left(0);
                    }
                });

            }

        }

        function getEventGeneratorFromObservableArray(observable, options){

            return ko.computed(function(){
                var obs = (observable() && observable().map(function(ev){

                        var overridedEnd = false;
                        if(ev.duration && ev.end){
                            overridedEnd = true;
                        }

                        if(!ev.duration && ev.end){
                            if (timeToDecimal(ev.end()) > timeToDecimal(ev.start())){
                                ev.duration = ko.observable(decimalToTime(timeToDecimal(ev.end()) - timeToDecimal(ev.start())));
                            }else {
                                ev.duration = ko.observable(decimalToTime((timeToDecimal(ev.end()) + 24) - timeToDecimal(ev.start())));
                            }
                        }

                        var event = new Event(ev.label, ev.start, ev.duration, ev.date, ev.category, ev.column, options.onSaveEvent);

                        if(overridedEnd){
                            event.overridedEnd(decimalToTime(timeToDecimal(ev.end())));
                        }
                        if(ev.id != void 0){
                            event.id = ev.id;
                        }
                        if(ev.item){
                            event.item = ev.item;
                        }
                        return event;
                    })) || [];
                return obs;
            }, this);
        }

        function ScheduleWidgetViewModel(params) {
            var self = this;
            this.eventsDefinition = params.events || ko.observableArray([]);
            this.infoEventsDefinition = params.info || ko.observableArray([]);

            this.contextMenuProvider = params.contextMenuProvider || function(){};
            this.options = $.extend({}, defaults, params.options || {});
            this.start = this.options.start;
            this.columnsDefinition = this.options.columns;
            this.duration = this.options.duration;
            this.events = getEventGeneratorFromObservableArray(this.eventsDefinition, this.options);
            this.onApiReady = params.onApiReady || function(){};

            refreshOverlaps(this.events);
            this.getTimeBlocks = ko.computed(function() {
                var blocks = [];
                var currentDate = moment(this.options.startDate()).add(-1, 'days');

                var blockSize = timeToDecimal(this.options.block());
                var startTime = timeToDecimal(this.start());
                var end = startTime + timeToDecimal(this.duration());

                for(var s = startTime; s < end; s = s + blockSize){
                    var hour = (s % 24);
                    var block = new TimeBlock(hour);

                    if(decimalToTime(hour) == decimalToTime(timeToDecimal(this.options.dayStartsAt()))){
                        currentDate.add(1, 'days');
                    }

                    block.date = currentDate.format('YYYY-MM-DD');
                    if((decimalToTime(hour) == decimalToTime(timeToDecimal(this.options.dayStartsAt()))) || blocks.length === 0){
                        block.displayDate = true;
                    }
                    block.id = currentDate.unix()+'_'+block.label;
                    blocks.push(block);
                }
                return blocks;

            }.bind(this));
            this.infoevents = getEventGeneratorFromObservableArray(this.infoEventsDefinition, this.options);
            this.getColumns = ko.computed(function(){
                if(!_columns){
                    _columns = this.columnsDefinition().map(function(c,i){
                        var col = new Column(c.name, i);
                        _columnsMapping[c.name] = col;
                        return col;
                    });
                }
                return _columns;
            }.bind(this));

            this.events.subscribe(function(evs){
                refreshOverlaps(evs);
                evs.map(function(e){
                    if(e.column){
                        e.column.subscribe(function(){
                            refreshOverlaps(self.events);
                        });
                    }
                });
            });

            this.events().map(function(e){
                if(e.column){
                    e.column.subscribe(function(){
                        refreshOverlaps(self.events);
                    });
                }
            });

            this.onApiReady({
                events: this.events,
                options: this.options,
                info: this.infoevents
            });
        }

        ScheduleWidgetViewModel.prototype.getDateStr = getDateStr;

        ScheduleWidgetViewModel.prototype.getColumnsWidth = function(){
            var w = 100 / this.columnsDefinition().length;
            return w && w.toString().slice(0,4) + '%';
        };

        ScheduleWidgetViewModel.prototype.getColumnDomId = function(column){
            return 'schedule-widget__column--'+column.id;
        };

        ScheduleWidgetViewModel.prototype.handleDragStart = function(scheduleEvent,jsEvent){
            _draggingEvent = scheduleEvent;
            _draggingEvent.takeStateSnapshot();
            return true;
        };

        ScheduleWidgetViewModel.prototype.handleDragOver = function(column, jsEvent){
            jsEvent.preventDefault();
        };

        ScheduleWidgetViewModel.prototype.handleDragEnter = function(column, jsEvent){
            if(_draggingEvent){
                _draggingEvent.column(column.name);
            }
            return true;
        };

        ScheduleWidgetViewModel.prototype.getDragEndHandler = function(vm){
            return function(event, jsEvent){
                if(event.isDirty()){
                    vm.options.onDropEventOnColumn(event);
                }
                _draggingEvent = null;
                return true;
            };
        };

        ScheduleWidgetViewModel.prototype.handleDrop = function(column, jsEvent){
            _draggingEvent = null;
            return true;
        };

        ScheduleWidgetViewModel.prototype.getEventActions = function(scheduleEvent) {
            var actions = [];
            if(this.contextMenuProvider) {
                actions = this.contextMenuProvider(scheduleEvent);
            }
            return actions;
        }

        ScheduleWidgetViewModel.prototype.getContextMenuHandler = function(scheduleEvent){
            var self = this;
            return function(scheduleEvent, jsEvent){
                if(self.contextMenuProvider){
                    var actions = self.contextMenuProvider(scheduleEvent);
                    contextualMenuDomElem.empty();
                    if(actions){
                        actions.forEach(function(action){
                            var item = contextualMenuItemDomElem.clone();
                            var icon = contextualMenuItemIconDomElem.clone();

                            item.bind('click', function(){
                                contextualMenuDomElem.hide();
                                action.action();
                            });

                            item.html(action.label);

                            if(action.faIcon){
                                icon.find('i').addClass(['fa',action.faIcon].join('-'));
                            }
                            item.prepend(icon);

                            contextualMenuDomElem.append(item);

                            contextualMenuDomElem.css({
                                top: jsEvent.pageY+'px',
                                left: jsEvent.pageX+'px'
                            });

                            $('body').append(contextualMenuDomElem);

                        });
                    }
                    locateIntoView(contextualMenuDomElem);
                    contextualMenuDomElem.show();
                }
            };
        };

        ScheduleWidgetViewModel.prototype.getTopOffsetFromStartTime = function(time, date){
            var blocks = this.getTimeBlocks();
            var count = 0;
            var blockTime = timeToDecimal(this.options.block());
            var eventTime = timeToDecimal(time);
            var hourTime = timeToDecimal('01:00:00:00');
            for (var i = 0; i < blocks.length; i++){
                var blockDate = blocks[i].date;
                if(blockDate != date){
                    count++;
                }else{
                    var t = blocks[i].time;
                    if(eventTime >= t && eventTime < t+blockTime){
                        break;
                    }
                    count++;
                }
            }
            return count*this.options.blockHeight()+((eventTime % blockTime) * this.options.blockHeight() * (Math.floor(hourTime / blockTime)))+'px';
        };

        ScheduleWidgetViewModel.prototype.getHeightFromDuration = function(duration) {
            var blockTime = timeToDecimal(this.options.block());
            var hourTime = timeToDecimal('01:00:00');
            var height = (timeToDecimal(duration) * (Math.floor(hourTime / blockTime) * this.options.blockHeight()));
            return height > 1 ? height : 2;
        };

        ScheduleWidgetViewModel.prototype.getEventTopOffset = function(ev){
            return this.getTopOffsetFromStartTime(ev.start(), ev.date());
        };

        ScheduleWidgetViewModel.prototype.getEventHeight = function(ev){
            return this.getHeightFromDuration(ev.duration());
        };

        window.scheduleWidget = {
            utils: {
                decimalToTime: decimalToTime,
                timeToDecimal: timeToDecimal
            }
        };

        return ScheduleWidgetViewModel;

    }
})(window);
