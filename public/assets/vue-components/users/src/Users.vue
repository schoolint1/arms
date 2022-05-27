<!-- npm run build -->
<template>
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
                <div v-for="user in clss.users" v-on:click="onSelectUser($event, user)" v-bind:class="[{ 'active-user': user.isSelected, 'disabled-user': user.isDisabled }, 'a-user']"><span>{{ user.name }}</span></div>
            </template>
        </template>
    </div>
</div>
</template>

<style>
    .a-user {
        cursor: pointer;
        border-left: 2px;
        padding-left: 5px;
        font-size: 90%;
    }
    .a-user:hover {
        border-left: 2px solid #007bff;
        padding-left: 3px;
    }
    .a-user:hover > span {
        border-bottom: #000 1px dotted;
    }
    .active-user {
        border-left: 2px solid #0b2e13;
        padding-left: 3px !important;
    }
    .disabled-user {
        text-decoration: line-through;
    }
</style>

<script>
export default {
    name: 'users',
    props: ['users'],
    data: function() {
      return {
        selectedUser: null
      };
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
        onSelectUser: function (event, user) {
            if(this.selectedUser != null) {
                this.selectedUser.isSelected = false;
            }
            
            this.selectedUser = user;
            this.$emit('select-user', user);
            
            if(!this.selectedUser.hasOwnProperty("isDisabled") || (this.selectedUser.isDisabled === false)) {
                this.selectedUser.isSelected = true;
            }
        }
    }
}
</script>
