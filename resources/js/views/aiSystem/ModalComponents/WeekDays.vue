<template>
    <b-row>
        <b-col sm="12">
        <table class="table table-responsive-sm week-days">
            <tbody>
            <tr v-for="(day, index) in value" :key="index">
                <td>{{day.title}}</td>
                <td>
                    <input-group>
                        <input-group-text>
                            С
                        </input-group-text>
                        <input-group-text class="input-full-width">
                                <model-list-select
                                        :class="{'form-control':true, 'is-invalid': validateState(index+'.from') === false,'search-select-invalid':validateState(index+'.from') === false}"
                                        :list="hours"
                                        v-model="day.from"
                                        option-value="name"
                                        option-text="name"
                                        :selected-option="day.from"
                                        :isDisabled=" ! day.enabled"
                                        placeholder="Выберите время">
                                </model-list-select>
                                <b-form-invalid-feedback :id="index+'.fromLiveFeedback'" >
                                    <template v-if="validateState(index+'.from') === false " v-for="nErr in errors[index+'.from']" >
                                        <span  v-text="nErr"></span><br>
                                    </template>
                                </b-form-invalid-feedback>
                        </input-group-text>
                    </input-group>
                </td>
                <td>

                    <input-group>
                        <input-group-text>
                            По
                        </input-group-text>
                        <input-group-text class="input-full-width">
                            <model-list-select
                                    :class="{'form-control':true, 'is-invalid': validateState(index+'.to') === false,'search-select-invalid':validateState(index+'.to') === false}"
                                    :list="hours"
                                    v-model="day.to"
                                    option-value="name"
                                    option-text="name"
                                    :selected-option="day.to"
                                    :isDisabled=" ! day.enabled"
                                    placeholder="Выберите время">
                            </model-list-select>
                            <b-form-invalid-feedback :id="index+'.toLiveFeedback'" >
                                <template v-if="validateState(index+'.to') === false " v-for="nErr in errors[index+'.to']" >
                                    <span  v-text="nErr"></span><br>
                                </template>
                            </b-form-invalid-feedback>
                        </input-group-text>
                    </input-group>
                </td>
                <td>
                    <label class="mb-0 mt-1 switch switch-lg switch-3d switch-primary">
                        <input class="switch-input" type="checkbox" checked="" v-model="day.enabled">
                        <span class="switch-slider"></span>
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
        </b-col>
        <b-col sm="12" v-if="isChanged" class="text-right">
            <b-button variant="success" @click="save()">
                Сохранить
            </b-button>
        </b-col>
    </b-row>
</template>

<script>

    import { ModelListSelect } from 'vue-search-select';
    import InputGroup from "bootstrap-vue/es/components/input-group/input-group";
    import InputGroupPrepend from "bootstrap-vue/es/components/input-group/input-group-prepend";
    import InputGroupText from "bootstrap-vue/es/components/input-group/input-group-text";

    export default {
        name: "week-days",
        components: {
            ModelListSelect,
            InputGroupText,
            InputGroupPrepend,
            InputGroup
        },
        props: {
            'value': {
                type: Array
            },
            'errors': {
                type: Object
            },
        },

        watch: {
            value: {
                deep: true,
                handler: function (val, lastVal) {
                    this.isChanged = true;
                    this.$emit('input', val);
                }
            }
        },

        mounted(){
            if( ! this.value) {
                this.$emit('input', Object.assign([], this.defaultWeekDays));
            }
        },

        data: () => {
            return {
                isChanged: false,
                defaultWeekDays: [
                    {title: 'Понедельник', from: null, to: null, enabled: true, order: 1},
                    {title: 'Вторник', from: null, to: null, enabled: true, order: 2},
                    {title: 'Среда', from: null, to: null, enabled: true, order: 3},
                    {title: 'Четверг', from: null, to: null, enabled: true, order: 4},
                    {title: 'Пятница', from: null, to: null, enabled: true, order: 5},
                    {title: 'Суббота', from: null, to: null, enabled: false, order: 6},
                    {title: 'Воскресенье', from: null, to: null, enabled: false, order: 7}
                ],
                hours: [
                    {name: '0:00'},
                    {name: '1:00'},
                    {name: '2:00'},
                    {name: '3:00'},
                    {name: '4:00'},
                    {name: '5:00'},
                    {name: '6:00'},
                    {name: '7:00'},
                    {name: '8:00'},
                    {name: '9:00'},
                    {name: '10:00'},
                    {name: '11:00'},
                    {name: '12:00'},
                    {name: '13:00'},
                    {name: '14:00'},
                    {name: '15:00'},
                    {name: '16:00'},
                    {name: '17:00'},
                    {name: '18:00'},
                    {name: '19:00'},
                    {name: '20:00'},
                    {name: '21:00'},
                    {name: '22:00'},
                    {name: '23:00'},
                ],
            }
        },
        methods: {
            save(){
                this.$emit('save');
                this.isChanged = false;
            },
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
        },
    }
</script>

<style scoped>
    .input-full-width{
        flex: 1 1 auto;
        width: 1%;
        display: block;
    }
    .table.week-days td{
        vertical-align: middle;
    }
    .table.week-days tr:first-child td{
        border-top: 0px;
    }
</style>