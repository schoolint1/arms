<div class="row mt-3" id="app-users">
    <div class="col-3">
        <div class="p-3 bg-light rounded shadow-sm">
            <h6 class="border-bottom border-gray pb-1 mb-0">Параллели</h6>
            <div class="pt-2">
                <span class="btn btn-sm mr-1 btn-list"
                        v-bind:class="parallel.check ? 'btn-primary': 'btn-secondary'"
                        v-for="(parallel, parallelIndex) in users"
                        v-on:click="checkparallel($event, parallelIndex)">
                    {{ parallelIndex }}
                </span>
            </div>

            <h6 class="border-bottom border-gray pb-1 pt-1 mb-0">Классы</h6>
            <div class="pt-2">
                <template v-for="(parallel, parallelIndex) in users" v-if="parallel.check">
                    <span class="btn btn-sm mr-1 btn-list"
                          v-bind:class="clss.check ? 'btn-primary': 'btn-secondary'"
                          v-for="(clss, classIndex) in parallel.classes"
                          v-on:click="checkclass($event, parallelIndex, classIndex)">
                        {{ classIndex }}
                    </span>
                </template>
            </div>

            <h6 class="border-bottom border-gray pb-1 pt-1 mb-0">Дети</h6>
            <div class="pt-2">
                <template v-for="parallel in users" v-if="parallel.check">
                    <template v-for="clss in parallel.classes" v-if="clss.check">
                        <div v-for="user in clss.users" v-on:click="getUserCommissionData($event, user)" v-bind:class="[{ 'active-user': user.isSelected }, 'a-user']"><span>{{ user.name }}</span></div>
                    </template>
                </template>
            </div>
        </div>
    </div>
    <div class="col-9">
        <div class="d-flex justify-content-center" v-if="isLoadCommissionUserData">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <ul class="nav nav-tabs" v-if="commission" role="tablist">
            <template v-for="(com, indexCom) in commission" v-if="commission">
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="home-tab" data-toggle="tab" v-bind:href="'#comission-num-' + indexCom" role="tab">{{ com.name }}<span v-if="com.isLock" class="comission-lock"> / Закрыта</span></a>
            </li>
            </template>
        </ul>
        <div class="tab-content" v-if="commission">
            <template v-for="(com, indexCom) in commission" v-if="commission">
                <div class="tab-pane fade" v-bind:id="'comission-num-' + indexCom" role="tabpanel">
                    <div class="p-3 mb-3 bg-light rounded shadow-sm">
                        <h4 class="border-bottom border-gray pb-1 pt-1 mb-0">{{ com.name }}<span v-if="com.isLock" class="comission-lock"> / Закрыта</span></h4>
                        <template v-for="(group, indexGroup) in commission_groups">
                            <h5 class="border-bottom border-gray pb-1 pt-1 mb-0">{{ group.name }}</h5>
                            <template v-for="(parameter, indexParameter) in group.parameters" v-if="((indexCom == 1) && parameter.isFirstCommissionAccess) || ((indexCom == 2) && parameter.isSecondCommissionAccess) || ((indexCom == 3) && parameter.isThirdCommissionAccess)">
                                <h6 class="pb-1 pt-1 mb-0">{{ parameter.name }}</h6>
                                <template  v-if="!group.access || com.isLock">
                                    <div v-if="commissionDataExist(indexCom, parameter.id)">{{ commission_data[indexCom][parameter.id].val }}</div>
                                    <div class="alert alert-secondary" role="alert" v-else>Записи нет</div>
                                </template>
                                <template v-else>
                                    <template v-if="commissionDataExist(indexCom, parameter.id)">{{ commission_data[indexCom][parameter.id].val }} </template>
                                    <span class="no-text" v-else>Записи нет</span>
                                    <button type="button" class="btn btn-sm btn-primary" v-on:click="showEditDialog($event, indexCom, group.id, parameter.id)">Редактировать</button>
                                </template>
                            </template>
                        </template>
                        <div style="margin-top: 1rem;">
                            <a v-bind:href="protocolURL" v-if="protocolURL != null">Скачать протокол</a>
                            <button type="button" v-bind:class="protocolIsGetting?'disabled':''" class="btn btn-sm btn-primary" v-on:click="getUserProtocol($event, indexCom)"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="protocolIsGetting"><span class="sr-only">Loading...</span></div> Получить протокол</button>
                        </div>
                    </div>
                    <div class="p-3 mb-3 bg-light rounded shadow-sm">
                        <h5 class="border-bottom border-gray pb-1 pt-1 mb-0">Рекомендации для &laquo;{{ classInfoName }}&raquo; класса</h5>
                        <template  v-if="com.isLock">
                            <div v-if="commissionClassDataExist(indexCom)">{{ commission_class_data[indexCom].val }}</div>
                            <div class="alert alert-secondary" role="alert" v-else>Записи нет</div>
                        </template>
                        <template v-else>
                            <template v-if="commissionClassDataExist(indexCom)">{{ commission_class_data[indexCom].val }} </template>
                            <span class="no-text" v-else>Записи нет</span>
                            <button type="button" class="btn btn-sm btn-primary" v-on:click="showEditClassDialog($event, indexCom)">Редактировать</button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

    </div>
    <modal v-if="showModal" @close="showModal = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Редактировать / {{ groupNameById }} / {{ parameterNameById }}</h5>

        <div slot="body">
            <div class="row" style="margin-bottom: 1rem;">
                <div class="col">
                    <textarea class="form-control" style="height: 100%;" v-model="modalValue"></textarea>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Поиск" v-model="modalFilter">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" @click="modalFilter = ''"><img src="/img/icon_delete.png" class="modal-icon" alt="Очистить"></button>
                            <button type="button" class="btn btn-outline-secondary" @click="showNewVariantDialog()"><img src="/img/icon_add.png" class="modal-icon" alt="Добавить"></button>
                        </div>
                    </div>
                    <div style="overflow-y: scroll; height: 300px; margin-top: 1rem;">
                        <template v-for="(variant, variantId) in variants">
                            <div v-if="variant.parameterId == modalParameterId" v-show="varfilter(variant.val)" class="modal-variant-block"><span @click="addToModal(variant.val)" class="modal-variant">{{ variant.val }}</span> <img src="/img/icon_edit.png" style="margin-left: 1rem;" @click="showEditVariantDialog($event, variantId)"> <img src="/img/icon_delete.png" @click="deleteVariant($event, variantId)"></div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="row">
                <label for="modalSpecialist" class="col-sm-2 col-form-label">Специалист</label>
                <div class="col-sm-6">
                    <select id="modalSpecialist" class="form-control" v-model="modalSpecialistId">
                        <option value="0">Нет</option>
                        <option v-for="(specialist, specialistId) in filterSpecialist" v-bind:value="specialistId">
                            {{ specialist.surname }} {{ specialist.firstname }} {{ specialist.patronymic }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" v-bind:class="modalIsSaving?'disabled':''" @click="saveData()"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalIsSaving"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="showModal = false">Закрыть</button>
        </template>
    </modal>

    <modal v-if="showVariantModal" @close="showVariantModal = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить / Изменить</h5>

        <div slot="body" class="row">
            <textarea class="form-control" style="height: 100%;" v-model="modalVarianValue"></textarea>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" v-bind:class="modalVarianIsSaving?'disabled':''" @click="saveVariantData()"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalVarianIsSaving"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="closeVariantDialog()">Закрыть</button>
        </template>
    </modal>

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
            users: <?php echo json_encode($users) ?>,
            variants: <?php echo json_encode($variants) ?>,
            groupsSpecialists: <?php echo json_encode($groupsSpecialists) ?>,
            specialists: <?php echo json_encode($specialists) ?>,
            commission: {},
            commission_groups: {},
            commission_data: {},

            class_info: {},
            commission_class_data: {},

            selectedUser: null,
            isLoadCommissionUserData: false,

            showModal: false,
            modalValue: '',
            modalId: null,
            modalParameterId: null,
            modalCommissionIndex: null,
            modalGroupId: null,
            modalIsSaving: false,
            modalFilter: '',
            modalSpecialistId: null,

            modalVarianValue: '',
            showVariantModal: false,
            modalVarianIsSaving: false,
            variantId: null,

            showClassModal: false,
            modalClassValue: '',
            modalClassIsSaving: false,
            modalClassCommissionIndex: null,

            protocolIsGetting: false,
            protocolURL: null
        },
        computed: {
            classInfoName: function () {
                if(this.class_info.hasOwnProperty('name')) {
                    return this.class_info['name'];
                }
                return '';
            },
            groupNameById: function () {
                if(this.modalGroupId == null) {
                    return '';
                }
                for(var group in this.commission_groups) {
                    if(this.commission_groups[group].id == this.modalGroupId) {
                        return this.commission_groups[group].name;
                    }
                }
                return '';
            },
            parameterNameById: function () {
                if(this.modalGroupId == null) {
                    return '';
                }
                if(this.modalParameterId == null) {
                    return '';
                }
                for(var group in this.commission_groups) {
                    if(this.commission_groups[group].id == this.modalGroupId) {
                        for (var parameter in this.commission_groups[group].parameters) {
                            if(this.commission_groups[group].parameters[parameter].id == this.modalParameterId) {
                                return this.commission_groups[group].parameters[parameter].name;
                            }
                        }
                    }
                }
                return '';
            },
            filterSpecialist: function () {
                var groupId = this.modalGroupId;
                var result = {};
                if(this.groupsSpecialists.hasOwnProperty(groupId)) {
                    var index;
                    for (index = 0; index < this.groupsSpecialists[groupId].length; ++index) {
                        if(this.specialists.hasOwnProperty(this.groupsSpecialists[groupId][index])) {
                            result[this.groupsSpecialists[groupId][index]] = this.specialists[this.groupsSpecialists[groupId][index]];
                        }
                    }
                }
                return result;
            }
        },
        methods: {
            getUserProtocol: function(event, indexCommission) {
                var _self = this;
                _self.protocolIsGetting = true;
                _self.protocolURL = null;

                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'ink_getuserdocument',
                        'params': {
                            'userId': _self.selectedUser.id,
                            'commissionNum': indexCommission
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
                            _self.protocolURL = data.result.uri;
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
                    _self.protocolIsGetting = false;
                });
            },
            showEditClassDialog: function(event, id) {
                this.showClassModal = true;
                this.modalClassIsSaving = false;
                this.modalClassCommissionIndex = id;
                if(this.commission_class_data.hasOwnProperty(id)) {
                    this.modalClassValue = this.commission_class_data[id].val;
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
                            'classId': _self.class_info.id,
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
                            _self.commission_class_data[_self.modalClassCommissionIndex]= {
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
            showNewVariantDialog: function() {
                this.showModal = false;
                this.showVariantModal = true;
                this.modalVarianValue = '';
                this.variantId = null;
            },
            showEditVariantDialog: function(event, id) {
                this.variantId = id;
                this.modalVarianValue = this.variants[id].val;
                this.showModal = false;
                this.showVariantModal = true;
            },
            closeVariantDialog: function() {
                this.showModal = true;
                this.showVariantModal = false;
            },
            saveVariantData: function() {
                var _self = this;
                _self.modalVarianIsSaving = true;

                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'save_commission_variant',
                        'params': {
                            'parameterId': _self.modalParameterId,
                            'val': _self.modalVarianValue,
                            'id': _self.variantId
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
                            if(_self.variantId == null) {
                                _self.variants[data.result.id] = {
                                    'parameterId': _self.modalParameterId,
                                    'val': data.result.val
                                };
                            } else {
                                _self.variants[data.result.id].val = data.result.val;
                            }
                            _self.closeVariantDialog();
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
                    _self.modalVarianIsSaving = false;
                });
            },
            deleteVariant: function(event, id) {
                if(!confirm('Удалить вариант?')) {
                    return;
                }
                var _self = this;

                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'delete_commission_variant',
                        'params': {
                            'id': id
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
                            _self.$delete(_self.variants, data.result.id);
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
                });
            },
            showEditDialog: function(event, indexCommission, groupId, parameterId) {
                this.modalGroupId = groupId;
                this.modalParameterId = parameterId;
                this.modalCommissionIndex = indexCommission;
                if(this.commission_data.hasOwnProperty(indexCommission)) {
                    if(this.commission_data[indexCommission].hasOwnProperty(parameterId)) {
                        this.modalValue = this.commission_data[indexCommission][parameterId].val;
                        this.modalId = this.commission_data[indexCommission][parameterId].id;
                        if(this.commission_data[indexCommission][parameterId].specialistId == null) {
                            this.modalSpecialistId = 0;
                        } else {
                            this.modalSpecialistId = this.commission_data[indexCommission][parameterId].specialistId;
                        }
                    } else {
                        this.modalValue = '';
                        this.modalId = null;
                        this.modalSpecialistId = 0;
                    }
                } else {
                    this.modalValue = '';
                    this.modalId = null;
                    this.modalSpecialistId = 0;
                }

                this.showModal = true;
            },
            saveData: function () {
                var _self = this;
                _self.modalIsSaving = true;
                
                if(_self.modalValue.trim().length == 0) {
                    new Noty({
                        type: 'error',
                        timeout: 6000,
                        text: 'Не заполнено решение комиссии',
                        animation: {
                            open : 'animated fadeInRight',
                            close: 'animated fadeOutRight'
                        }
                    }).show();
                    _self.modalIsSaving = false;
                    return;
                }
                
                if(_self.modalSpecialistId == null) {
                    new Noty({
                        type: 'error',
                        timeout: 6000,
                        text: 'Не выбран специалист',
                        animation: {
                            open : 'animated fadeInRight',
                            close: 'animated fadeOutRight'
                        }
                    }).show();
                    _self.modalIsSaving = false;
                    return;
                }

                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'save_commission_data',
                        'params': {
                            'userId': _self.selectedUser.id,
                            'commissionNum': _self.modalCommissionIndex,
                            'parameterId': _self.modalParameterId,
                            'val': _self.modalValue,
                            'id': _self.modalId,
                            'specialistId': _self.modalSpecialistId
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
                            if(!_self.commission_data.hasOwnProperty(_self.modalCommissionIndex)) {
                                _self.commission_data[_self.modalCommissionIndex] = {};
                            }
                            if(!_self.commission_data[_self.modalCommissionIndex].hasOwnProperty(_self.modalParameterId)) {
                                _self.commission_data[_self.modalCommissionIndex][_self.modalParameterId] = {};
                            }
                            _self.commission_data[_self.modalCommissionIndex][_self.modalParameterId] = {
                                'val': data.result.val,
                                'id': data.result.id,
                                'specialistId': data.result.specialistId
                            };

                            _self.showModal = false;
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
                    _self.modalIsSaving = false;
                });
            },
            addToModal: function(str) {
                if(this.modalValue.length > 0) {
                    this.modalValue += ', ' + str;
                } else {
                    this.modalValue = str;
                }
            },
            commissionDataExist: function (indexCom, parameterId) {
                if(!this.commission_data.hasOwnProperty(indexCom)) return false;
                if(!this.commission_data[indexCom].hasOwnProperty(parameterId)) return false;
                if(!this.commission_data[indexCom][parameterId].hasOwnProperty('val')) return false;
                return (this.commission_data[indexCom][parameterId].val.length == 0)? false : true;
            },
            commissionClassDataExist: function(indexCom) {
                if(!this.commission_class_data.hasOwnProperty(indexCom)) return false;
                return (this.commission_class_data[indexCom].val.length == 0)? false : true;
            },
            varfilter: function(str) {
                if(this.modalFilter == '') {
                    return true;
                }
                return str.toLowerCase().indexOf(this.modalFilter.toLowerCase()) >= 0? true : false;
            },
            checkparallel: function (event, parallelIndex) {
                this.users[parallelIndex].check = !this.users[parallelIndex].check;
            },
            checkclass: function (event, parallelIndex, classIndex) {
                this.users[parallelIndex].classes[classIndex].check = !this.users[parallelIndex].classes[classIndex].check;
            },
            getUserCommissionData: function (event, user) {
                var _self = this;
                if(_self.selectedUser != null) {
                    _self.selectedUser.isSelected = false;
                }
                _self.selectedUser = user;
                _self.selectedUser.isSelected = true;
                _self.isLoadCommissionUserData = true;

                _self.commission = null;
                _self.commission_groups = null;
                _self.commission_data = null;

                _self.modalSpecialistId = null;

                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'ink_user_commission_data',
                        'params': {
                            'userId': user.id
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
                            _self.commission_data = data.result.commission_data;
                            _self.commission_groups = data.result.commission_groups;
                            _self.commission = data.result.commission;
                            _self.class_info = data.result.class_info;
                            _self.commission_class_data = data.result.commission_class_data;
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
                    _self.isLoadCommissionUserData = false;
                });
            }
        }
    })
</script>
