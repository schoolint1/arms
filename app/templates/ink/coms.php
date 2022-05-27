<div id="app-commissions" class="mt-3">
<template v-for="(commission, commissionlIndex) in commissions">
    <div class="p-3 mb-1 bg-light rounded">
        {{ commission.name }} год / <span v-if="commission.isCreate">
            Первая комиссия <button class="btn btn-sm"
                                    v-bind:class="commission.isFirstLock ? 'btn-secondary' : 'btn-primary'"
                                    v-on:click="commissionLock($event, commission.id, 1)">
                {{ commission.isFirstLock ? 'Разблокирвать' : 'Заблокировать' }}

            </button> <v-date-picker
                    v-bind:value="getDate(commission.firstDate)"
                    @dayclick="setDate($event, {index: commissionlIndex, num: 1, oldDate: commission.firstDate})"
                    :popover="{ placement: 'bottom', visibility: 'click' }">
                <button class="btn btn-sm btn-primary">
                    {{ showDate(commission.firstDate) }}
                </button>
            </v-date-picker>
            Вторая комиссия <button class="btn btn-sm"
                                    v-bind:class="commission.isSecondLock ? 'btn-secondary' : 'btn-primary'"
                                    v-on:click="commissionLock($event, commission.id, 2)">
                {{ commission.isSecondLock ? 'Разблокирвать' : 'Заблокировать' }}
            </button> <v-date-picker
                    v-bind:value="getDate(commission.secondDate)"
                    @dayclick="setDate($event, {index: commissionlIndex, num: 2, oldDate: commission.secondDate})"
                    :popover="{ placement: 'bottom', visibility: 'click' }">
                <button class="btn btn-sm btn-primary">
                    {{ showDate(commission.secondDate) }}
                </button>
            </v-date-picker>
            Третья комиссия <button class="btn btn-sm"
                                    v-bind:class="commission.isThirdLock ? 'btn-secondary' : 'btn-primary'"
                                    v-on:click="commissionLock($event, commission.id, 3)">
                {{ commission.isThirdLock ? 'Разблокирвать' : 'Заблокировать' }}
            </button> <v-date-picker
                    v-bind:value="getDate(commission.thirdDate)"
                    @dayclick="setDate($event, {index: commissionlIndex, num: 3, oldDate: commission.thirdDate})"
                    :popover="{ placement: 'bottom', visibility: 'click' }">
                <button class="btn btn-sm btn-primary">
                    {{ showDate(commission.thirdDate) }}
                </button>
            </v-date-picker>
        </span>
        <button v-else class="btn btn-sm btn-dark" v-on:click="createCommission($event, commission.id)">Создать</button>
    </div>
</template>
</div>

<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-commissions',
        data: {
            commissions: <?php echo json_encode($commissions) ?>,
            yearId: <?=$container->get('session')->getSchoolYear()['id'] ?>
        },
        methods: {
            getDate: function (date) {
                if(date == null) {
                    return new Date();
                }
                if(date instanceof Date) {
                    return date;
                }
                return new Date(date);
            },
            setDate: function (event, obj) {
                if((dateFormat(this.getDate(obj.oldDate), 'yyyy-mm-dd') != dateFormat(event.date, 'yyyy-mm-dd')) || (obj.oldDate == null)) {
                    var _self = this;
                    $.ajax({
                        type: 'POST',
                        async: true,
                        url: '/api',
                        data: JSON.stringify({
                            'jsonrpc': '2.0',
                            'method': 'date_commission',
                            'params': {
                                'id': _self.commissions[obj.index].id,
                                'num': obj.num,
                                'date': dateFormat(event.date, 'yyyy-mm-dd')
                            },
                            'id': 1
                        }),
                        contentType: "application/json; charset=utf-8",
                        dataType: 'json',
                        success: function (data) {
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
                                    for (var i=0; i<_self.commissions.length; i++) {
                                        if(_self.commissions[i].id == data.result.id) {
                                            switch (data.result.num) {
                                                case 1:
                                                    _self.commissions[i].firstDate = data.result.date;
                                                    break;
                                                case 2:
                                                    _self.commissions[i].secondDate = data.result.date;
                                                    break;
                                                case 3:
                                                    _self.commissions[i].thirdDate = data.result.date;
                                                    break;
                                            }
                                            console.info('ok');
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
                        }
                    })
                }
            },
            showDate: function (_date) {
                if(_date == null) {
                    return 'Нет';
                }
                var date = new Date(_date);
                return (new Intl.DateTimeFormat().format(date));
            },
            createCommission: function(event, commissionId) {
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'create_commission',
                        'params': {
                            'id': commissionId
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json',
                    success: function (data) {
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
                                for (var i=0; i<_self.commissions.length; i++) {
                                    if(_self.commissions[i].id == data.result.id) {
                                        _self.commissions[i].isCreate = data.result.isCreate;
                                        _self.commissions[i].isFirstLock = true;
                                        _self.commissions[i].isSecondLock = true;
                                        _self.commissions[i].isThirdLock = true;
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
                    }
                })
            },
            commissionLock: function (event, commissionId, commissionNum) {
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'lock_commission',
                        'params': {
                            'id': commissionId,
                            'num': commissionNum
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json',
                    success: function (data) {
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
                                for (var i=0; i<_self.commissions.length; i++) {
                                    if(_self.commissions[i].id == data.result.id) {
                                        switch (data.result.num) {
                                            case 1:
                                                _self.commissions[i].isFirstLock = data.result.isLock;
                                                break;
                                            case 2:
                                                _self.commissions[i].isSecondLock = data.result.isLock;
                                                break;
                                            case 3:
                                                _self.commissions[i].isThirdLock = data.result.isLock;
                                                break;
                                        }
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
                    }
                })
            }
        }
    })
</script>