<div class="row mt-3" id="app-positions">
    <div class="col">
        <div class="head-content">
            <div></div>
            <button type="button" class="btn btn-lg btn-primary" :disabled="modalPosition.isShow" v-on:click="onModalPositionNew">Добавить должность</button>
        </div>
        <div class="pt-2">
            <div class="position-item" v-for="(group, grouplIndex) in groups">
                <span v-for="indexLevel in group.level">-</span>
                {{ group.name }} 
                <button type="button" :disabled="group.isGroupArmDisable" v-on:click="onSetARMStatus(group)" class="btn btn-sm" v-bind:class="[ isGroupArmOn(group.id) ? 'btn-primary' : 'btn-secondary' ]">Есть АРМ</button>
                <div class="btn-group btn-group-sm" role="group" aria-label="Доступ">
                    <button v-for="modul in modules" type="button" class="btn" :disabled="group.isGroupAccessDisable" v-on:click="onSetAccessSatus(group, modul)" v-bind:class="[ isAccess(group.id, modul) ? 'btn-primary' : 'btn-secondary' ]">{{ modul }}</button>
                </div>
                <span class="position-item_edit">
                <button type="button" class="btn btn-sm btn-primary" v-on:click="onModalPositionEdit(group)">Редактировать</button>
                <button type="button" :disabled="group.isGroupDelDisable" class="btn btn-sm btn-danger" v-on:click="onPositionDel(group)">Удалить?</button>
                </span>
            </div>
        </div>
    </div>
    
    <modal v-if="modalPosition.isShow" @close="modalPosition.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить / Изменить</h5>

        <div slot="body">
            <div class="row">
                <div class="col">
                    <label for="modalPositionParent" class="form-label">Родительская должность</label>
                    <select class="form-control" v-model="modalPosition.parentId" id="modalPositionParent">
                        <option value="0">Нет</option>
<?php foreach ($groups as $proup): ?>
                        <option value="<?= $proup['id'] ?>"><?= $proup['name'] ?></option>
<?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="modalPositionName" class="form-label">Название</label>
                    <input class="form-control" id="modalPositionName" type="text" v-model="modalPosition.name">
                </div>
            </div>
        </div>
        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalPosition.isSave" @click="onPositionSaveData"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalPosition.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalPosition.isShow = false">Закрыть</button>
        </template>
    </modal>
</div>

<script src="/assets/vue-components/modal/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-positions',
        data: {
            'groups': <?php echo json_encode($groups) ?>,
            'specialists': <?php echo json_encode($specialists) ?>,
            'accessList': <?php echo json_encode($accessList) ?>,
            'modules': <?php echo json_encode($container->get('molulesConfig')) ?>,
            
            modalPosition: {
                isShow: false,
                isSave: false,
                'name': '',
                parentId: 0,
                id: 0
            }
        },
        methods: {
            isGroupArmOn: function(id) {
                return this.specialists.includes(id);
            },
            isAccess: function(id, modulName) {
                if(!this.accessList.hasOwnProperty(id)) {
                    return false;
                }
                for (let value in this.accessList[id]) {
                    if(this.accessList[id][value] == modulName) {
                        return true;
                    }
                }
                return false;
            },
            onSetARMStatus: function(group) {
                this.$set(group, 'isGroupArmDisable', true);
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Positions_setARM',
                        'params': {
                            'groupId': group.id,
                            'status': _self.isGroupArmOn(group.id) ? 'delete' : 'insert'
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
                            if(_self.isGroupArmOn(group.id)) {
                                for( let i = 0; i < _self.specialists.length; i++){ 
                                    if ( _self.specialists[i] === group.id) { 
                                        _self.specialists.splice(i, 1);
                                        break;
                                    }
                                }
                            } else {
                                _self.specialists.push(group.id);
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
                    _self.$set(group, 'isGroupArmDisable', false);
                });
            },
            onSetAccessSatus: function(group, modulName) {
                this.$set(group, 'isGroupAccessDisable', true);
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Positions_setModulAccess',
                        'params': {
                            'groupId': group.id,
                            'modul': modulName,
                            'status': _self.isAccess(group.id, modulName) ? 'delete' : 'insert'
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
                            _self.accessList = data.result.accessList;
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
                    _self.$set(group, 'isGroupAccessDisable', false);
                });
            },
            onModalPositionNew: function() {
                this.isDisabledAddBtn = true;
                this.modalPosition.isShow = true;
                this.modalPosition.id = 0;
                this.modalPosition.name = '';
                this.modalPosition.parentId = 0;
            },
            onPositionSaveData: function() {
                if(this.modalPosition.id == 0) {
                    this.onPositionInsert();
                } else {
                    this.onPositionUpdate();
                }
            },
            onModalPositionEdit: function(group) {
                this.modalPosition.isShow = true;
                this.modalPosition.id = group.id;
                this.modalPosition.name = group.name;
                this.modalPosition.parentId = group.parentId;
            },
            onPositionInsert: function() {
                var _self = this;
                _self.modalPosition.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Positions_insert',
                        'params': {
                            'parentId': _self.modalPosition.parentId,
                            'name': _self.modalPosition.name
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
                            _self.modalPosition.isShow = false;
                            _self.groups = data.result.groups;
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
                    _self.modalPosition.isSave = false;
                });
            },
            onPositionUpdate: function() {
                var _self = this;
                _self.modalPosition.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Positions_update',
                        'params': {
                            'id': _self.modalPosition.id,
                            'parentId': _self.modalPosition.parentId,
                            'name': _self.modalPosition.name
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
                            _self.modalPosition.isShow = false;
                            _self.groups = data.result.groups;
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
                    _self.modalPosition.isSave = false;
                });
            },
            onPositionDel: function(group) {
                if (confirm("Удалить должность?") == false) {
                    return;
                }
                this.$set(group, 'isGroupDelDisable', true);
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Positions_delete',
                        'params': {
                            'id': group.id
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
                        _self.$set(group, 'isGroupDelDisable', false);
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            _self.groups = data.result.groups;
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
                            _self.$set(group, 'isGroupDelDisable', false);
                        }
                    }
                })
            }
        }
    })
</script>
