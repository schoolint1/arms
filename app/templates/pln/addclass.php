<div id="app-form" class="container">
    <div class="row mt-3">
        <div class="col-6">
            <classes 
                v-bind:classes="classes"
                v-on:select-class="onSelectClass"></classes>
        </div>
        <div class="col-6">
            <div class="p-3 bg-light rounded shadow-sm">
                <h6 class="border-bottom border-gray pb-1 mb-0">Выбранные классы</h6>
                <div class="pt-2">
                    <div v-for="(itemClass, indexClass) in selectedClasses" v-on:click="onRemoveClass($event, itemClass)" class="a-class"><span>{{ itemClass.name }} класс</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="p-3 bg-light rounded shadow-sm" style="margin-top: 1rem;margin-bottom: 1rem;">
        <div class="row mt-3">
            <div class="col">
                <div class="form-inline">
                    <div class="form-group">
                        <label for="exampleFormControlSelect1">Тип активности</label>
                        <select class="form-control" id="exampleFormControlSelect1" v-model="plan.paramActivityType">
                            <option value="1">Учебная активность</option>
                            <option value="2">Внеурочная активность</option>
                            <option value="3">Реабилитация</option>
                        </select>
                    </div>
                    <div class="form-group" v-if="plan.paramActivityType == 3">
                        <label for="specialists">Специалист</label>
                        <select v-model="plan.paramActivitySpecialist" class="form-control" id="specialists">
                            <option v-for="(itemSpecialist, indexSpecialist) in specialists" v-bind:value="indexSpecialist">
                              {{ itemSpecialist }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group" v-if="plan.paramActivityType == 2">
                        <label for="comment">Комментарий</label>
                        <input type="text" id="comment" v-model="plan.paramActivityComment">
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <div class="form-inline">
                С <v-date-picker
                    v-model="plan.paramFrom"
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
                ПО <v-date-picker
                    v-model="plan.paramTo"
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
                </div>
            </div>
        </div>
        <!-- Понедельник -->
        <div class="row mt-3">
            <div class="col form-inline pln-chk-time">
                <div class="form-group form-check">
                    <label class="form-check-label" for="plan-time-mn-check">ПН</label>
                    <input type="checkbox" class="form-check-input" id="plan-time-mn-check" v-model="plan.time.mn.check">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-mn-from">С</label>
                    <input type="time" class="form-control" id="plan-time-mn-from" v-model="plan.time.mn.paramFrom">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-mn-to">ПО</label>
                    <input type="time" class="form-control" id="plan-time-mn-to" v-model="plan.time.mn.paramTo">
                </div>
            </div>
        </div>
        <!-- Вторник -->
        <div class="row mt-3">
            <div class="col form-inline pln-chk-time">
                <div class="form-group form-check">
                    <label class="form-check-label" for="plan-time-tu-check">ВТ</label>
                    <input type="checkbox" class="form-check-input" id="plan-time-tu-check" v-model="plan.time.tu.check">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-tu-from">С</label>
                    <input type="time" class="form-control" id="plan-time-tu-from" v-model="plan.time.tu.paramFrom">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-tu-to">ПО</label>
                    <input type="time" class="form-control" id="plan-time-tu-to" v-model="plan.time.tu.paramTo">
                </div>
            </div>
        </div>
        <!-- Среда -->
        <div class="row mt-3">
            <div class="col form-inline pln-chk-time">
                <div class="form-group form-check">
                    <label class="form-check-label" for="plan-time-we-check">СР</label>
                    <input type="checkbox" class="form-check-input" id="plan-time-we-check" v-model="plan.time.we.check">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-we-from">С</label>
                    <input type="time" class="form-control" id="plan-time-we-from" v-model="plan.time.we.paramFrom">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-we-to">ПО</label>
                    <input type="time" class="form-control" id="plan-time-we-to" v-model="plan.time.we.paramTo">
                </div>
            </div>
        </div>
        <!-- Четверг -->
        <div class="row mt-3">
            <div class="col form-inline pln-chk-time">
                <div class="form-group form-check">
                    <label class="form-check-label" for="plan-time-th-check">ЧТ</label>
                    <input type="checkbox" class="form-check-input" id="plan-time-th-check" v-model="plan.time.th.check">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-th-from">С</label>
                    <input type="time" class="form-control" id="plan-time-th-from" v-model="plan.time.th.paramFrom">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-th-to">ПО</label>
                    <input type="time" class="form-control" id="plan-time-th-to" v-model="plan.time.th.paramTo">
                </div>
            </div>
        </div>
        <!-- Пятница -->
        <div class="row mt-3">
            <div class="col form-inline pln-chk-time">
                <div class="form-group form-check">
                    <label class="form-check-label" for="plan-time-fr-check">ПТ</label>
                    <input type="checkbox" class="form-check-input" id="plan-time-fr-check" v-model="plan.time.fr.check">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-fr-from">С</label>
                    <input type="time" class="form-control" id="plan-time-fr-from" v-model="plan.time.fr.paramFrom">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-fr-to">ПО</label>
                    <input type="time" class="form-control" id="plan-time-fr-to" v-model="plan.time.fr.paramTo">
                </div>
            </div>
        </div>
        <!-- Суббота -->
        <div class="row mt-3">
            <div class="col form-inline pln-chk-time">
                <div class="form-group form-check">
                    <label class="form-check-label" for="plan-time-sa-check">СБ</label>
                    <input type="checkbox" class="form-check-input" id="plan-time-sa-check" v-model="plan.time.sa.check">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-sa-from">С</label>
                    <input type="time" class="form-control" id="plan-time-sa-from" v-model="plan.time.sa.paramFrom">
                </div>
                <div class="form-group mb-2">
                    <label for="plan-time-sa-to">ПО</label>
                    <input type="time" class="form-control" id="plan-time-sa-to" v-model="plan.time.sa.paramTo">
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <button type="button" class="btn btn-lg btn-primary" v-on:click="onAddPlan($event)">Добавить расписание</button>
            </div>
        </div>
    </div>
</div>
<script src="/assets/vue-components/classes/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-form',
        data: {
            classes: <?php echo json_encode($classes) ?>,
            selectedClasses: [],
            specialists: <?php echo json_encode($specialists) ?>,
            plan: {
                paramFrom: '<?=$container->get('session')->getDate()->format('Y-m-d') ?>',
                paramTo: '<?=$container->get('session')->getDate()->format('Y-m-d') ?>',
                paramActivityType: 0,
                paramActivityComment: '',
                paramActivitySpecialist: 0,
                'time': {
                    'mn': { // Понедельник
                        'check': false,
                        'paramFrom': null,
                        'paramTo': null
                    },
                    'tu': { // Вторник
                        'check': false,
                        'paramFrom': null,
                        'paramTo': null
                    },
                    'we': { // Среда
                        'check': false,
                        'paramFrom': null,
                        'paramTo': null
                    },
                    'th': { // Четверг
                        'check': false,
                        'paramFrom': null,
                        'paramTo': null
                    },
                    'fr': { // Пятница
                        'check': false,
                        'paramFrom': null,
                        'paramTo': null
                    },
                    'sa': { // Суббота
                        'check': false,
                        'paramFrom': null,
                        'paramTo': null
                    }
                }
            },
                    
            modalDateConfig: {
                type: 'string',
                mask: 'YYYY-MM-DD', // Uses 'iso' if missing
            },
            
            minSchoolYearDate: new Date('<?=  $container->get('session')->getSchoolYearBeginDate()->format('Y-m-d') ?>'),
            maxSchoolYearDate: new Date('<?=  $container->get('session')->getSchoolYearEndDate()->format('Y-m-d') ?>')
        },
        computed: {

        },
        methods: {
            onSelectClass: function(clss) {
                if(!clss.isDisabled) {
                    this.selectedClasses.push(clss);
                    this.$set(clss,'isDisabled', true);
                }
            },
            onRemoveClass: function(event, clss) {
                clss.isDisabled = false;
                for(var i = 0; i < this.selectedClasses.length; i++){ 
                    if (this.selectedClasses[i] == clss) { 
                        this.selectedClasses.splice(i, 1); 
                    }
                }
            },
            onAddPlan: function(event) {
                event.target.disabled = true;
                var su = [];
                for (let index in this.selectedClasses) {
                    su.push(this.selectedClasses[index].id);
                }
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'pln_Plan_addClasses',
                        'params': {
                            'classes': su,
                            'plan': _self.plan
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
                    event.target.disabled = false;
                });
            }
        }
    })
</script>
