<template>
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
            <div v-for="(classItem, classIndex) in parallel.classes"
                 v-bind:class="[{ 'active-class': classItem.isSelected, 'disabled-class': classItem.isDisabled }, 'a-class']"
                 v-on:click="onSelectClass($event, classItem)">
                <span>{{ classItem.name }} класс</span>
            </div>
        </template>
    </div>
</div>
</template>

<style>
    .a-class {
        cursor: pointer;
        border-left: 2px;
        padding-left: 5px;
        font-size: 90%;
    }
    .a-class:hover {
        border-left: 2px solid #007bff;
        padding-left: 3px;
    }
    .a-class:hover > span {
        border-bottom: #000 1px dotted;
    }
    .active-class {
        border-left: 2px solid #0b2e13;
        padding-left: 3px !important;
    }
    .disabled-class {
        text-decoration: line-through;
    }
</style>

<script>
export default {
    name: 'classes',
    props: ['classes'],
    data: function() {
        return {
            selectedClass: null
        };
    },
    computed: {

    },
    methods: {
        checkparallel: function (event, parallelIndex) {
            this.classes[parallelIndex].check = !this.classes[parallelIndex].check;
        },
        onSelectClass: function (event, clss) {
            if(this.selectedClass != null) {
                this.selectedClass.isSelected = false;
            }
            
            this.selectedClass = clss;
            this.$emit('select-class', clss);
            
            if(!this.selectedClass.hasOwnProperty("isDisabled") || (this.selectedClass.isDisabled === false)) {
                this.selectedClass.isSelected = true;
            }
        }
    }
}
</script>