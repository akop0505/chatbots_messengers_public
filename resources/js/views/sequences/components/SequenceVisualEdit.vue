<template>
    <b-row>
        <b-col sm="4">
            <b-button class="btn-block d-flex align-items-center"
                v-for="(element, index) in elementTemplates"
                :key="index"
                @click="addElement(index)"
            >
                    <i class="font-2xl pr-2"
                       v-bind:class="element.icon"></i>
                    {{element.title}}
            </b-button>
        </b-col>
        <b-col sm="8">
            <task-list lockAxis="y"
                       v-model="elements"
                       :useDragHandle="true"
                       :transitionDuration="150"
                       :distance="10"
            >
                <task v-for="(element, index) in elements"
                      :index="index"
                      :key="index"
                      v-model="elements[index]"
                      :projectId = "projectId"
                ></task>
            </task-list>
        </b-col>
    </b-row>
</template>


<script>
    import axios from 'axios';
    import { ModelListSelect } from 'vue-search-select';
    import Button from "bootstrap-vue/es/components/button/button";
    import { sequencesElements } from '../../../configs/sequences';
    import TaskList from "./TaskList";
    import Task from "./Task";

    export default {
        props: {
            'projectId': {},
            'value': {
                type: Object,
                required: true,
            },
        },
        components: {
            Task,
            TaskList,
            Button,
            ModelListSelect
        },
        updated(){
        },
        mounted() {
        },
        data: function () {
            return {
                elementTemplates: sequencesElements,
            }
        },
        computed: {
            elements: {
                get:  function () {
                    return this.options.tasks;
                },
                set: function(val){
                    return this.options.tasks = val;
                }
            },
            options: {
                get: function() {
                    return this.value;
                },
                set: function(val){
                    this.$emit('input', val);
                }
            }
        },
        methods: {
            addElement(index){
                this.elements.push(Object.assign({}, this.elementTemplates[index]));
            }
        },
    }
</script>

<style scoped>

</style>