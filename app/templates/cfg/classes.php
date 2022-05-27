<div class="row mt-3" id="app-classes">
    <div class="col-2">
        <h6 class="border-bottom border-gray pb-1 mb-0">Учебные года</h6>
        <button v-for="yearItem in years" type="button" class="btn btn-block" v-bind:class="[ selectedYear != null && yearItem.id == selectedYear.id ? 'btn-primary' : 'btn-secondary' ]" v-on:click="onSelectYear(yearItem)">{{ yearItem.name }}</button>
    </div>
    <div class="col-10">
        <div class="head-content">
            <div><template v-if="selectedYear != null">Учебный год {{ selectedYear.name }} / Классов {{ countClasses }}</template></div>
            <button type="button" class="btn btn-lg btn-primary" :disabled="isDisabledAddBtn" v-on:click="onModalClassNew">Добавить класс</button>
        </div>

        <div v-for="(parallelItem, parallelIndex) in getYearClasses" class="row" style="border-bottom: 1px solid #dee2e6;"> 
            <div class="col-1 align-self-center">
                {{ parallelIndex }}
            </div>
            <div class="col-11">
                <div class="row align-items-center" v-for="(classItem, classIndex) in parallelItem">
                    <div class="class-item col">
                        {{ classItem.name }} 
                        <span class="class-item_edit">
                            <button type="button" class="btn btn-sm btn-primary" v-on:click="onModalClassEdit(classItem, parallelIndex)">Редактировать</button>
                            <button type="button" :disabled="classItem.isClassDelDisable" class="btn btn-sm btn-danger" v-on:click="onClassDel(classItem)">Удалить?</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <modal v-if="modalClass.isShow" @close="modalClass.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить / Изменить</h5>

        <div slot="body">
            <div class="row">
                <div class="col">
                    <label for="modalClassParallel" class="form-label">Параллель</label>
                    <select class="form-control" v-model="modalClass.parallel" id="modalClassParallel">
                        <option v-for="option in 11" v-bind:value="option">
                            {{ option }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="modalClassName" class="form-label">Название</label>
                    <input class="form-control" id="modalClassName" type="text" v-model="modalClass.name">
                </div>
            </div>
        </div>
        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalClass.isSave" @click="onPositionSaveData"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalClass.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalClass.isShow = false">Закрыть</button>
        </template>
    </modal>
</div>
<script src="/assets/vue-components/modal/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-classes',
        data: {
            'classes': <?php echo json_encode($classes) ?>,
            'years': <?php echo json_encode($years) ?>,
            'selectedYear': null,
            isDisabledAddBtn: true,
            
            modalClass: {
                isShow: false,
                isSave: false,
                'name': '',
                parallel: 0,
                id: 0
            }
        },
        filters: {
            reverse: function(value) {
                // slice to make a copy of array, then reverse the copy
                return value.slice().reverse();
            }
        },
        computed: {
            countClasses: function() {
                if(this.selectedYear == null) {
                    return 0;
                }
                let count = 0;
                for(let item in this.classes[this.selectedYear.id]) {
                    count += this.classes[this.selectedYear.id][item].length;
                }
                return count;
            },
            getYearClasses: function() {
                if(this.selectedYear == null) {
                    return null;
                }
                return this.classes[this.selectedYear.id];
            }
        },
        methods: {
            onSelectYear: function(year) {
                this.selectedYear = year;
                this.isDisabledAddBtn = false;
            },
            onModalClassNew: function() {
                this.modalClass.isShow = true;
                this.modalClass.isSave = false;
                this.modalClass.id = 0;
                this.modalClass.parallel = 1;
                this.modalClass.name = '';
            },
            onClassDel: function(classItem) {
                if (confirm("Удалить класс?") == false) {
                    return;
                }
                this.$set(classItem, 'isClassDelDisable', true);
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Classes_delete',
                        'params': {
                            'id': classItem.id
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
                        _self.$set(classItem, 'isClassDelDisable', false);
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            _self.classes = data.result.classes;
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
                            _self.$set(classItem, 'isClassDelDisable', false);
                        }
                    }
                })
            },
            onModalClassEdit: function(cls, parallel) {
                this.modalClass.isShow = true;
                this.modalClass.isSave = false;
                this.modalClass.id = cls.id;
                this.modalClass.parallel = parallel;
                this.modalClass.name = cls.name;
            },
            onPositionSaveData: function() {
                if(this.modalClass.id == 0) {
                    this.onPositionInsert();
                } else {
                    this.onPositionUpdate();
                }
            },
            onPositionInsert: function() {
                var _self = this;
                _self.modalClass.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Classes_insert',
                        'params': {
                            'parallel': _self.modalClass.parallel,
                            'name': _self.modalClass.name,
                            'yearId': _self.selectedYear.id
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
                            _self.modalClass.isShow = false;
                            _self.classes = data.result.classes;
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
                    _self.modalClass.isSave = false;
                });
            },
            onPositionUpdate: function() {
                var _self = this;
                _self.modalClass.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Classes_update',
                        'params': {
                            'id': _self.modalClass.id,
                            'parallel': _self.modalClass.parallel,
                            'name': _self.modalClass.name
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
                            _self.modalClass.isShow = false;
                            _self.classes = data.result.classes;
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
                    _self.modalClass.isSave = false;
                });
            }
        }
    });
</script>