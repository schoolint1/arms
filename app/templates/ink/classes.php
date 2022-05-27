<div class="row mt-3" id="app-users">
    <div class="col-3">
        <div class="p-3 bg-light rounded shadow-sm">
            <h6 class="border-bottom border-gray pb-1 mb-0">Параллели</h6>
            <div class="pt-2">
                <span class="btn btn-sm mr-1 btn-list"
                      v-bind:class="parallel.check ? 'btn-primary': 'btn-secondary'"
                      v-for="(parallel, parallelIndex) in classes"
                      v-on:click="checkparallel($event, parallelIndex)">
                    {{ parallelIndex }}
                </span>
            </div>

            <h6 class="border-bottom border-gray pb-1 pt-1 mb-0">Классы</h6>
            <div class="pt-2">
                <template v-for="(parallel, parallelIndex) in classes" v-if="parallel.check">
                    <span class="btn btn-sm mr-1 btn-list"
                          v-bind:class="(clss.id == selectedClassId) ? 'btn-primary': 'btn-secondary'"
                          v-for="(clss, classIndex) in parallel.classes"
                          v-on:click="getClassCommissionData($event, clss.id)">
                        {{ clss.name }}
                    </span>
                </template>
            </div>
        </div>
    </div>
    <div class="col-9">
        <div class="d-flex justify-content-center" v-if="isLoadCommissionClassData">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <template v-for="(com, indexCom) in commission" v-if="commission">
            <div class="p-3 mb-3 bg-light rounded shadow-sm">
                <h4 class="border-bottom border-gray pb-1 pt-1 mb-0">{{ com.name }}<span v-if="com.isLock" class="comission-lock"> / Закрыта</span></h4>
                <template  v-if="com.isLock">
                    <div v-if="commissionClassDataExist(indexCom)">{{ commission[indexCom].val }}</div>
                    <div class="alert alert-secondary" role="alert" v-else>Записи нет</div>
                </template>
                <template v-else>
                    <template v-if="commissionClassDataExist(indexCom)">{{ commission[indexCom].val }} </template>
                    <span class="no-text" v-else>Записи нет</span>
                    <button type="button" class="btn btn-sm btn-primary" v-on:click="showEditClassDialog($event, indexCom)">Редактировать</button>
                </template>
            </div>
        </template>

    </div>

    <modal v-if="showClassModal" @close="showClassModal = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить / Изменить</h5>

        <div slot="body" class="row">
            <textarea class="form-control" style="height: 100%;" v-model="modalClassValue"></textarea>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" v-bind:class="modalClassIsSaving?'disabled':''" @click="saveClassData()"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalClassIsSaving"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="closeClassDialog()">Закрыть</button>
        </template>
    </modal>
</div>

<!-- template for the modal component -->
<script type="text/x-template" id="modal-template">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">

                    <div class="modal-header">
                        <slot name="header">
                            default header
                        </slot>
                    </div>

                    <div class="modal-body">
                        <slot name="body">
                            default body
                        </slot>
                    </div>

                    <div class="modal-footer">
                        <slot name="footer">
                            default footer
                            <button class="btn btn-primary" @click="$emit('close')">Закрыть</button>
                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</script>

<script type="text/javascript">
    // register modal component
    Vue.component('modal', {
        template: '#modal-template'
    });

    var app2 = new Vue({
        el: '#app-users',
        data: {
            classes: <?php echo json_encode($classes) ?>,
            selectedClassId: null,

            isLoadCommissionClassData: false,

            commission: {},

            showClassModal: false,
            modalClassValue: '',
            modalClassIsSaving: false,
            modalClassCommissionIndex: null
        },
        methods: {
            commissionClassDataExist: function(indexCom) {
                if(!this.commission.hasOwnProperty(indexCom)) return false;
                return (this.commission[indexCom].val.length == 0)? false : true;
            },
            showEditClassDialog: function(event, id) {
                this.showClassModal = true;
                this.modalClassIsSaving = false;
                this.modalClassCommissionIndex = id;
                if(this.commission.hasOwnProperty(id)) {
                    this.modalClassValue = this.commission[id].val;
                } else {
                    this.modalClassValue = '';
                }
            },
            closeClassDialog: function() {
                this.showClassModal = false;
                this.modalClassIsSaving = false;
            },
            saveClassData: function() {
                var _self = this;
                _self.modalClassIsSaving = true;

                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'save_commission_class_data',
                        'params': {
                            'classId': _self.selectedClassId,
                            'commissionNum': _self.modalClassCommissionIndex,
                            'val': _self.modalClassValue
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
                            _self.commission[_self.modalClassCommissionIndex]= {
                                'val': data.result.val,
                                'id': data.result.id
                            };

                            _self.showClassModal = false;
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
                    _self.modalClassIsSaving = false;
                });
            },
            checkparallel: function (event, parallelIndex) {
                this.classes[parallelIndex].check = !this.classes[parallelIndex].check;
            },
            getClassCommissionData: function (event, classId) {
                var _self = this;
                _self.isLoadCommissionClassData = true;
                _self.selectedClassId = classId;

                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'ink_class_commission_data',
                        'params': {
                            'classId': _self.selectedClassId
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
                            _self.commission = data.result.commission;
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
                    _self.isLoadCommissionClassData = false;
                });
            }
        }
    })
</script>
