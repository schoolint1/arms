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
        <!-- Рекомендации -->
    </div>

</div>

<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-users',
        data: {
            users: <?php echo json_encode($users) ?>,
            commission: {},
            commission_groups: {},
            commission_data: {},

            class_info: {},
            commission_class_data: {},

            selectedUser: null,
            isLoadCommissionUserData: false,
        },
        computed: {

        },
        methods: {
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
