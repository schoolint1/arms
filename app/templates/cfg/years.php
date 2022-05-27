<div class="row mt-3" id="app-years">
    <div class="col">
        <div class="head-content">
            <div></div>
            <button type="button" class="btn btn-lg btn-primary" :disabled="modalYear.isShow" v-on:click="onModalYearNew">Добавить год</button>
        </div>
        <div class="pt-2">
            <div class="year-item" v-for="(year, yearIndex) in years">
                {{ year.name }} <span class="year-item_date">( {{ strDate(year.begindate) }} )</span>

                <span class="year-item_edit">
                    <button type="button" class="btn btn-sm btn-primary" v-on:click="onModalYearEdit(year)">Редактировать</button>
                    <button type="button" :disabled="year.isDelDisable" class="btn btn-sm btn-danger" v-on:click="onYearDel(year)">Удалить?</button>
                </span>
            </div>
        </div>
    </div>
    
    <modal v-if="modalYear.isShow" @close="modalYear.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить / Изменить</h5>

        <div slot="body">
            <div class="row">
                <div class="col-2">
                   <label class="form-label">Дата начала</label>
                   <v-date-picker
                    v-model="modalYear.begindate"
                    :model-config="modalDateConfig"
                    :popover="{ placement: 'bottom' }">
                    <template v-slot="{ inputValue, togglePopover }">
                        <div class="input-group">
                            <input type="text" class="form-control" readonly :value="inputValue">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" @click="togglePopover ()">
                                    <img src="/img/icon-calendar.svg" style="width: 1rem; height: 1rem; margin-bottom: 3px;" alt="Календарь">
                                </button>
                            </div>
                        </div>
                    </template>
                </v-date-picker>
                </div>
                <div class="col">
                    <label for="modalYearName" class="form-label">Название</label>
                    <input class="form-control" id="modalYearName" type="text" v-model="modalYear.name">
                </div>
            </div>
        </div>
        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalYear.isSave" @click="onYearSaveData"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalYear.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalYear.isShow = false">Закрыть</button>
        </template>
    </modal>
</div>

<script src="/assets/vue-components/modal/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-years',
        data: {
            'years': <?php echo json_encode($years) ?>,
            
            modalYear: {
                isShow: false,
                isSave: false,
                'name': '',
                begindate: null,
                id: 0
            },
                    
            modalDateConfig: {
                type: 'string',
                mask: 'YYYY-MM-DD', // Uses 'iso' if missing
            }
        },
        methods: {
            strDate: function(date) {
                return DateTime.fromSQL(date).toFormat('dd.LL.yyyy');
            },
            onModalYearNew: function() {
                this.modalYear.isShow = true;
                this.modalYear.isSave = false;
                this.modalYear.id = 0;
                this.modalYear.name = '';
                this.modalYear.begindate = null;
            },
            onYearSaveData: function() {
                if(this.modalYear.id == 0) {
                    this.onYearInsert();
                } else {
                    this.onYearUpdate();
                }
            },
            onModalYearEdit: function(year) {
                this.modalYear.isShow = true;
                this.modalYear.isSave = false;
                this.modalYear.id = year.id;
                this.modalYear.name = year.name;
                this.modalYear.begindate = year.begindate;
            },
            onYearInsert: function() {
                var _self = this;
                _self.modalYear.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Years_insert',
                        'params': {
                            'name': _self.modalYear.name,
                            'begindate': _self.modalYear.begindate
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
                            _self.modalYear.isShow = false;
                            _self.years.push(data.result.year);
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
                    _self.modalYear.isSave = false;
                });
            },
            onYearUpdate: function() {
                var _self = this;
                _self.modalYear.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Years_update',
                        'params': {
                            'id': _self.modalYear.id,
                            'name': _self.modalYear.name,
                            'begindate': _self.modalYear.begindate
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
                            _self.modalYear.isShow = false;
                            for( let i = 0; i < _self.years.length; i++){
                                if(_self.years[i].id == _self.modalYear.id) {
                                    _self.years[i].name = _self.modalYear.name;
                                    _self.years[i].begindate = _self.modalYear.begindate;
                                    break;
                                }
                            }
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
                    _self.modalYear.isSave = false;
                });
            },
            onYearDel: function(year) {
                if (confirm("Удалить год?") == false) {
                    return;
                }
                this.$set(year, 'isDelDisable', true);
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Year_delete',
                        'params': {
                            'id': year.id
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
                        _self.$set(year, 'isDelDisable', false);
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            for( let i = 0; i < _self.years.length; i++){
                                if(_self.years[i].id == _self.modalYear.id) {
                                    _self.years.splice(i, 1);
                                    break;
                                }
                            }
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
                            _self.$set(year, 'isDelDisable', false);
                        }
                    }
                })
            }
        }
    })
</script>
