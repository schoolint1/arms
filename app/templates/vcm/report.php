<div class="row mt-3" id="app-reports">
    <div class="col">
        <div class="head-content">
            <div>
                <div class="head-content__selectedUser-name"><?= $name ?></div>
                <div class="head-content__Zakl">Заключение № <?= $docNumber ?> от <?= $docDate ?></div>
            </div>
            <button type="button" class="btn btn-lg btn-primary" v-on:click="onModalExtreportNewItem">Добавить</button>
        </div>
        <template v-for="(item, index) in extreportItems">
            <div class="extreportitem-item">
                <div class="extreportitem-item_head">
                    <div>
                        <span v-if="item.isNeed == 1" class="badge badge-success app-badget"><img src="/img/icon_ok.svg" alt="Нужен" width="16"></span>
                        <span v-else class="badge badge-secondary app-badget"><img src="/img/icon_no.svg" alt="Не нужен" width="16"></span>
                        <span class="app-badget-outline"> {{ specialists[item.specialistId] }}</span>
                    </div>
                    <div class="btn-group" role="group" aria-label="Управление записями протокола">
                        <button type="button" class="btn btn-primary btn-sm" @click="onExtreportItemGet($event, index)">Редактировать</button>
                        <button type="button" class="btn btn-danger btn-sm" :disabled="item.isDisabled" @click="onExtreportItemDelete($event, index)">Удалить</button>
                    </div>
                </div>
                <div>{{ item.recom }}</div>
            </div>
        </template>
    </div>
    
    <modal v-if="modalExtreport.isShow" @close="modalExtreport.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить / Изменить</h5>

        <div slot="body">
            <div class="row">
                <div class="col">
                    <label>Нуждается ли в специалисте?</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="modalExtreportSpecNeed" id="modalExtreportSpecNeed-NO" value="0" v-model="modalExtreport.specNeed">
                            <label class="form-check-label" for="modalExtreportSpecNeed-NO">
                                Не нуждается
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="modalExtreportSpecNeed" id="modalExtreportSpecNeed-YES" value="1" v-model="modalExtreport.specNeed">
                            <label class="form-check-label" for="modalExtreportSpecNeed-YES">
                                Нуждается
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <label for="modalExtreportSpec">Специалист</label>
                    <select class="custom-select" id="modalExtreportSpec" v-model="modalExtreport.specId">
                        <option selected>Специалист...</option>
                        <option v-for="(item , index) in specialists" v-bind:value="index" >
                            {{item }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="modalExtreportRecom">Рекомендации</label>
                    <textarea class="form-control" id="modalExtreportRecom" placeholder="Рекомендации..." v-model="modalExtreport.recom"></textarea>
                </div>
            </div>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalExtreport.isSave" @click="onExtreportItemSaveData"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalExtreport.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalExtreport.isShow = false">Закрыть</button>
        </template>
    </modal>
</div>

<script src="/assets/vue-components/modal/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">

    var app2 = new Vue({
        el: '#app-reports',
        data: {
            modalExtreport: {
                isShow: false,
                isSave: false,
                specNeed: null,
                specId: null,
                recom: '',
                id: 0
            },
            specialists: <?php echo json_encode($specialists) ?>,
            reportId: <?= $reportId ?>,
            extreportItems: <?php echo json_encode($extreportItems) ?>
        },
        methods: {
            onExtreportItemSaveData: function() {
                if(this.modalExtreport.id == 0) {
                    this.onExtreportItemAddData();
                } else {
                    this.onExtreportItemUpdateData();
                }
            },
            onExtreportItemAddData: function() {
                var _self = this;
                
                this.modalExtreport.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'vcm_ExtreportsItems_add',
                        'params': {
                            'reportId': _self.reportId,
                            'isNeed': _self.modalExtreport.specNeed,
                            'specialistId': _self.modalExtreport.specId,
                            'recom': _self.modalExtreport.recom
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
                            _self.modalExtreport.isShow = false;
                            _self.extreportItems.push({
                                'id': data.result.id,
                                'isNeed': _self.modalExtreport.specNeed,
                                'specialistId': _self.modalExtreport.specId,
                                'recom': _self.modalExtreport.recom
                            });
                            _self.modalExtreport.specNeed = null;
                            _self.modalExtreport.specId = null;
                            _self.modalExtreport.recom = '';
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
                    _self.modalExtreport.isSave = false;
                });
            },
            onExtreportItemUpdateData: function() {
                var _self = this;
                this.modalExtreport.isSave = true;
                
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'vcm_ExtreportsItems_update',
                        'params': {
                            'id': _self.modalExtreport.id,
                            'isNeed': _self.modalExtreport.specNeed,
                            'specialistId': _self.modalExtreport.specId,
                            'recom': _self.modalExtreport.recom
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
                            _self.modalExtreport.isShow = false;
                            // Обновление
                            for(let i = 0; i < _self.extreportItems.length; i++) {
                                if(_self.extreportItems[i].id == _self.modalExtreport.id) {
                                    _self.extreportItems[i].isNeed = _self.modalExtreport.specNeed;
                                    _self.extreportItems[i].specialistId = _self.modalExtreport.specId;
                                    _self.extreportItems[i].recom = _self.modalExtreport.recom;
                                    break;
                                }
                            }
                            // Очистка формы
                            _self.modalExtreport.id = 0;
                            _self.modalExtreport.specNeed = null;
                            _self.modalExtreport.specId = null;
                            _self.modalExtreport.recom = '';
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
                    _self.modalExtreport.isSave = false;
                });
            },
            onExtreportItemGet: function(event, index) {
                this.modalExtreport.id = this.extreportItems[index].id;
                this.modalExtreport.specNeed = this.extreportItems[index].isNeed;
                this.modalExtreport.specId = this.extreportItems[index].specialistId;
                this.modalExtreport.recom = this.extreportItems[index].recom;
                this.modalExtreport.isShow = true;
            },
            onExtreportItemDelete: function(event, index) {
                if(!confirm('Удалить запись?')) {
                    return;
                }
                var _self = this;
                var id = this.extreportItems[index].id;
                this.$set(this.extreportItems[index],'isDisabled', true);
                
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'vcm_ExtreportsItems_delete',
                        'params': {
                            'id': id,
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
                        _self.extreportItems[index].isDisabled = false;
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            _self.extreportItems.splice(index, 1);
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
                            _self.extreportItems[index].isDisabled = false;
                        }
                    }
                })
            },
            onModalExtreportNewItem: function() {
                this.modalExtreport.isShow = true;
            }
        }
    });
</script>