<div id="app-plan" class="row mt-3 mr-1">
    <div class="col-3">
        <users 
            v-bind:users="users"
            v-on:select-user="onSelectUser"></users>
    </div>
    <div class="col-9 mb-1">
        <div class="d-flex justify-content-center" v-if="isLoad">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="weekNumberCiontrol">Учебная неделя</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button class="btn btn-primary" type="button" v-on:click="onChangeWeekPrev($event)">< Предыдущая</button>
                        </div>
                        <select class="form-control" id="weekNumberCiontrol" v-model="weekNumber" @change="onChangeWeek($event)">
                            <option v-for="(itemWeek, indexWeek) in weeks" v-bind:value="indexWeek">
                                № {{ indexWeek }} с {{ itemWeek.begin }} по {{ itemWeek.end }}
                            </option>
                        </select>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" v-on:click="onChangeWeekNext($event)">Следующая ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-2 plan-week-title">
                <h4 class="text-center">ПН</h4>
            </div>
            <div class="col-2 plan-week-title">
                <h4 class="text-center">ВТ</h4>
            </div>
            <div class="col-2 plan-week-title">
                <h4 class="text-center">СР</h4>
            </div>
            <div class="col-2 plan-week-title">
                <h4 class="text-center">ЧТ</h4>
            </div>
            <div class="col-2 plan-week-title">
                <h4 class="text-center">ПТ</h4>
            </div>
            <div class="col-2 plan-week-title">
                <h4 class="text-center">СБ</h4>
            </div>
        </div>
        <div class="row">
            <div class="plan-time-marker-group">
                <div class="plan-time-marker" v-for="i in 13">{{ i + 7 }}:00</div>
            </div>
            <div class="plan-time-line-group">
                <div class="plan-time-line" v-for="i in 13"></div>
            </div>
            <div class="col-2 plan-week-line" v-for="i in 6" v-on:click="onShowModalReportind(i)">
                <template v-if="(plan != null) && (plan[i] != 'undefined')">
                    <div v-bind:class="['user-activity', {'user-activity-type-1': (item.activityType == 1), 'user-activity-type-2': (item.activityType == 2), 'user-activity-type-3': (item.activityType == 3)}]" 
                         v-for="item in plan[i]" v-bind:style="{ top: (item.top - 8 * 60) + 'px', height: item.len + 'px' }"
                         v-on:click.stop="onShowReport($event, item.id, item.planFor)"
                         v-on:mouseenter="onPopShow" v-on:mouseleave="onPopHide">
                        <div v-if="item.activityType == 1" class="user-activity__type">Учебная деятельность</div>
                        <div v-if="item.activityType == 2" class="user-activity__type">Внеучебная деятельность</div>
                        <div v-if="item.activityType == 3" class="user-activity__type">Реабилитация</div>
                        
                        <div class="user-activity-info">
                            <span v-if="item.activitySpecialist > 0">{{ specialists[item.activitySpecialist] }}</span>
                            <div class="user-activity__time">с {{ item.timeFrom }} по {{ item.timeTo }}</div>
                        </div>
                    </div>
                </template>
            </div>
            
        </div>
    </div>
    
    <modal v-if="modalReport.isShow" @close="modalReport.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Подробнее...</h5>

        <div slot="body">
            <template v-if="modalReport.report != null">
            <div>Расписание с {{ modalReport.report.dateFrom }} по {{ modalReport.report.dateTo }}</div>
            <div v-if="modalReport.report.activityType == 1">Учебная деятельность</div>
            <div v-if="modalReport.report.activityType == 2">Внеучебная деятельность</div>
            <div v-if="modalReport.report.activityType == 3">Реабилитация <span v-if="modalReport.report.activitySpecialist > 0">( {{ specialists[modalReport.report.activitySpecialist] }} - {{ modalReport.report.specialistFIO }})</span></div>
            <div>{{ modalReport.report.activityComment }}</div>
            </template>
        </div>
        <template slot="footer">
            
                <div class="col">
                    <button class="btn btn-danger" v-if="!modalReport.isDeleteProcess" @click="modalReport.isDeleteProcess = true">Удалить?</button>
                    <button class="btn btn-danger" v-if="modalReport.isDeleteProcess" :disabled="modalReport.isSave" @click="onDeletePlan"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalReport.isSave"><span class="sr-only">Loading...</span></div>Удалить!</button>
                    <button class="btn btn-primary" v-if="modalReport.isDeleteProcess"  :disabled="modalReport.isSave" @click="modalReport.isDeleteProcess = false">Не удалять</button>
                </div>
                <div class="col text-right"><button class="btn btn-primary" @click="modalReport.isShow = false">Закрыть</button></div>
            
        </template>
    </modal>
    
    <modal v-if="modalReportind.isShow" @close="modalReportind.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить...</h5>

        <div slot="body">
            <div class="row">
                <div class="col">
                    День недели: <strong class="ml-1">
                        <template v-if="modalReportind.weekDayNumber == 1">ПН</template>
                        <template v-if="modalReportind.weekDayNumber == 2">ВТ</template>
                        <template v-if="modalReportind.weekDayNumber == 3">СР</template>
                        <template v-if="modalReportind.weekDayNumber == 4">ЧТ</template>
                        <template v-if="modalReportind.weekDayNumber == 5">ПТ</template>
                        <template v-if="modalReportind.weekDayNumber == 6">СБ</template>
                    </strong>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col form-inline">
                    <v-date-picker
                        v-model="modalReportind.paramDateFrom"
                        :min-date="minSchoolYearDate"
                        :max-date="maxSchoolYearDate"
                        :model-config="modalDateConfig"
                        :popover="{ placement: 'bottom' }">
                        <template v-slot="{ inputValue, togglePopover }">
                            <div class="input-group">
                                <input type="text" readonly id="modalReportDate" class="form-control" :value="inputValue">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-primary" @click="togglePopover()">
                                        <img src="/img/icon-calendar.svg" style="width: 1rem; height: 1rem; margin-bottom: 3px;" alt="Календарь">
                                    </button>
                                </div>
                            </div>
                        </template>
                    </v-date-picker>
                    <input type="time" class="form-control ml-1" v-model="modalReportind.paramTimeFrom">
                    <span style="margin-left: 1rem; margin-right: 1rem;">-</span>
                    <v-date-picker
                        v-model="modalReportind.paramDateTo"
                        :min-date="minSchoolYearDate"
                        :max-date="maxSchoolYearDate"
                        :model-config="modalDateConfig"
                        :popover="{ placement: 'bottom' }">
                        <template v-slot="{ inputValue, togglePopover }">
                            <div class="input-group">
                                <input type="text" readonly id="modalReportDate" class="form-control" :value="inputValue">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-primary" @click="togglePopover ()">
                                        <img src="/img/icon-calendar.svg" style="width: 1rem; height: 1rem; margin-bottom: 3px;" alt="Календарь">
                                    </button>
                                </div>
                            </div>
                        </template>
                    </v-date-picker>
                    <input type="time" class="form-control ml-1" v-model="modalReportind.paramTimeTo">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col">
                    <div class="form-inline">
                        <div class="form-group">
                            <label for="paramActivityType">Тип активности</label>
                            <select class="form-control ml-1" id="paramActivityType" v-model="modalReportind.paramActivityType">
                                <option value="1">Учебная активность</option>
                                <option value="2">Внеурочная активность</option>
                                <option value="3">Реабилитация</option>
                            </select>
                        </div>
                        <div class="form-group ml-1" v-if="modalReportind.paramActivityType == 2">
                            <label for="comment">Комментарий</label>
                            <input type="text" id="comment" v-model="modalReportind.paramActivityComment">
                        </div>
                        <div class="form-group ml-1" v-if="modalReportind.paramActivityType == 3">
                            <label for="specialists">Специалист</label>
                            <select v-model="modalReportind.paramActivitySpecialist" class="form-control ml-1" id="specialists">
                                <option v-for="(itemSpecialist, indexSpecialist) in specialists" v-bind:value="indexSpecialist">
                                  {{ itemSpecialist }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalReportind.isSave" @click="onAddReport"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalReportind.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalReportind.isShow = false">Закрыть</button>
        </template>
    </modal>
</div>

<script src="/assets/vue-components/users/dist/build.js" type="text/javascript"></script>
<script src="/assets/vue-components/modal/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-plan',
        data: {
            users: <?php echo json_encode($users) ?>,
            selectedUser: null,
            weeks: <?php echo json_encode($weeks) ?>,
            specialists: <?php echo json_encode($specialists) ?>,
            weekNumber: <?php echo $weekNumber ?>,
            isLoad: false,
            plan: null,
            popper: null,
            
            modalReport: {
                isShow: false,
                isSave: false,
                isDeleteProcess: false,
                id: null,
                planFor: '',
                report: null
            },
                    
            modalReportind: {
                isShow: false,
                isSave: false,
                paramDateFrom: null,
                paramDateTo: null,
                paramTimeFrom: '',
                paramTimeTo: '',
                paramActivityType: 0,
                paramActivitySpecialist: 0,
                paramActivityComment: '',
                weekDayNumber: 0
            },
                    
            modalDateConfig: {
                type: 'string',
                mask: 'YYYY-MM-DD', // Uses 'iso' if missing
            },
            minSchoolYearDate: new Date('<?=  $container->get('session')->getSchoolYearBeginDate()->format('Y-m-d') ?>'),
            maxSchoolYearDate: new Date('<?=  $container->get('session')->getSchoolYearEndDate()->format('Y-m-d') ?>')
        },
        methods: {
            onPopShow: function(event) {
                this.popper = new Popper(event.target, event.target.querySelector('.user-activity-info'),{
                    placement:  'top',
                    modifiers: {
                        offset: {
                          offset: "1,10"
                        }
                    }
                });
                this.popper.popper.classList.add("user-activity-info__show");
            },
            onPopHide: function(event) {
                if (this.popper) {
                    this.popper.popper.classList.remove("user-activity-info__show");
                    this.popper.destroy();
                    this.popper = null;
                }
            },
            onShowModalReportind: function(weekDayNumber) {
                if(this.selectedUser != null) {
                    this.modalReportind.isShow = true;
                    this.modalReportind.weekDayNumber = weekDayNumber;
                    this.modalReportind.paramDateFrom = this.weeks[this.weekNumber].beginSQL;
                    this.modalReportind.paramDateTo = this.weeks[this.weekNumber].endSQL;
                }
            },
            onAddReport: function() {
                var _self = this;
                _self.modalReportind.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'pln_Plan_addUser',
                        'params': {
                            'userId': _self.selectedUser.id,
                            'dateFrom': _self.modalReportind.paramDateFrom,
                            'timeFrom': _self.modalReportind.paramTimeFrom,
                            'dateTo': _self.modalReportind.paramDateTo,
                            'timeTo': _self.modalReportind.paramTimeTo,
                            'weekDayNumber': _self.modalReportind.weekDayNumber,
                            'activityType': _self.modalReportind.paramActivityType,
                            'activitySpecialist': _self.modalReportind.paramActivitySpecialist,
                            'activityComment': _self.modalReportind.paramActivityComment
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json'
                })
                .done(function (data) {
                    if (typeof data.error !== "undefined") {
                        new Noty({
                            type: 'error',
                            timeout: 6000,
                            text: data.error.message,
                            animation: {
                                open : 'animated fadeInRight',
                                close: 'animated fadeOutRight'
                            }
                        }).show();
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            new Noty({
                                type: 'success',
                                timeout: 6000,
                                text: 'Расписание добавлено',
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                            _self.modalReportind.isShow = false;
                            _self.getPlan();
                        }
                        if (data.result.status == 'error') {
                            new Noty({
                                type: 'error',
                                timeout: 6000,
                                text: data.result.message,
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                    }
                })
                .always(function () {
                    _self.modalReportind.isSave = false;
                });
            },
            onChangeWeek: function() {
                if(this.selectedUser != null) {
                    this.getPlan();
                }
            },
            onChangeWeekPrev: function() {
                if(this.weeks.hasOwnProperty(this.weekNumber - 1)) {
                    this.weekNumber -= 1;
                    this.getPlan();
                }
            },
            onChangeWeekNext: function() {
                if(this.weeks.hasOwnProperty(this.weekNumber + 1)) {
                    this.weekNumber += 1;
                    this.getPlan();
                }
            },
            onSelectUser: function(user) {
                this.selectedUser = user;
                this.getPlan();
            },
            getPlan: function() {
                var _self = this;
                _self.isLoad = true;
                _self.plan = null;

                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'pln_Plan_get',
                        'params': {
                            'userId': _self.selectedUser.id,
                            'weekNumber': _self.weekNumber
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json'
                })
                .done(function (data) {
                    if (typeof data.error !== "undefined") {
                        new Noty({
                            type: 'error',
                            timeout: 6000,
                            text: data.error.message,
                            animation: {
                                open : 'animated fadeInRight',
                                close: 'animated fadeOutRight'
                            }
                        }).show();
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            _self.plan = data.result.plan;
                        }
                        if (data.result.status == 'error') {
                            new Noty({
                                type: 'error',
                                timeout: 6000,
                                text: data.result.message,
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                    }
                })
                .always(function () {
                    _self.isLoad = false;
                });
            },
            onShowReport: function(event, id, planFor) {
                this.modalReport.id = id;
                this.modalReport.planFor = planFor;
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'pln_Plan_getReport',
                        'params': {
                            'planId': id,
                            'planFor': planFor
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json'
                })
                .done(function (data) {
                    console.log(data);
                    if (typeof data.error !== "undefined") {
                        new Noty({
                            type: 'error',
                            timeout: 6000,
                            text: data.error.message,
                            animation: {
                                open : 'animated fadeInRight',
                                close: 'animated fadeOutRight'
                            }
                        }).show();
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            _self.modalReport.report = data.result.report;
                            _self.modalReport.isShow = true;
                        }
                        if (data.result.status == 'error') {
                            new Noty({
                                type: 'error',
                                timeout: 6000,
                                text: data.result.message,
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                    }
                })
                .always(function () {
                    _self.isLoad = false;
                });
            },
            onDeletePlan: function() {
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'pln_Plan_delete',
                        'params': {
                            'planId': _self.modalReport.id,
                            'planFor': _self.modalReport.planFor
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json'
                })
                .done(function (data) {
                    console.log(data);
                    if (typeof data.error !== "undefined") {
                        new Noty({
                            type: 'error',
                            timeout: 6000,
                            text: data.error.message,
                            animation: {
                                open : 'animated fadeInRight',
                                close: 'animated fadeOutRight'
                            }
                        }).show();
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            _self.modalReport.isShow = false;
                            _self.modalReport.report = null;
                            _self.getPlan();
                        }
                        if (data.result.status == 'error') {
                            new Noty({
                                type: 'error',
                                timeout: 6000,
                                text: data.result.message,
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                    }
                })
                .always(function () {
                    _self.isLoad = false;
                    _self.modalReport.isDeleteProcess = false;
                });
            }
        },
        created: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const userId = urlParams.get("userId");
            console.log('User = ' + userId);
            if (userId != null) {
                for (var propParallel in this.users) {
                    for (var propClass in this.users[propParallel].classes) {
                        for (var propUser in this.users[propParallel].classes[propClass].users) {

                            if (this.users[propParallel].classes[propClass].users[propUser].id == userId) {

                                this.selectedUser = this.users[propParallel].classes[propClass].users[propUser];
                                this.selectedUser.isSelected = true;
                                this.users[propParallel].classes[propClass].check = true;
                                this.users[propParallel].check = true;
                                this.getPlan();
                                break;
                            }

                        }
                    }
                }
            }
        }
    })
</script>