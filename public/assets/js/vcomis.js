
    // register modal component
    Vue.component('modal', {
        template: '#modal-template'
    });

    var app2 = new Vue({
        el: '#app-users',
        data: {
            users: users,

            selectedUser: null,
            isLoad: false,
            
            showAddZaklModal: false,
            modalAddZaklIsSave: false,
            modalAddZaklDate: data,
            modalAddZaklNum: ''
        },
        computed: {
            isDisabledAddBtn() {
                return this.selectedUser == null ? true : false;
            },
            modalAddZaklGetDate: function () {
                return dateFormat(this.modalAddZaklDate, 'dd.mm.yyyy');
            }
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
            },
            showZakl: function(event) {
                this.showAddZaklModal = true;
            }
        }
    })