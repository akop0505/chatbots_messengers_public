<template>
    <b-row>
        <b-col sm="12">
            <b-form-group :state="validateState('timezone')"
                          aria-describedby="timezoneLiveFeedback">
                <label>Временная зона</label>
                <model-list-select
                        :class="{'form-control':true, 'is-invalid': validateState('timezone') === false,'search-select-invalid':validateState('timezone') === false}"
                        :list="timezones"
                        option-value="id"
                        option-text="name"
                        :selected-option="timezone"
                        v-model="timezone"
                        placeholder="Выберите временную зону">
                </model-list-select>
                <b-form-invalid-feedback id="timezoneLiveFeedback" >
                    <template v-if="validateState('timezone') === false " v-for="nErr in errors" >
                        <span  v-text="nErr"></span><br>
                    </template>
                </b-form-invalid-feedback>
            </b-form-group>
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

    export default {
        name: "timezone",
        components: {
            ModelListSelect
        },
        props: {
            'value': {
                required: true
            },
            'errors': {
                type: Object
            },
        },

        watch: {
            value: {
                handler: function (val, lastVal) {
                    this.timezone = val;
                }
            },
            timezone: function(val){
                if(this.value !== val) {
                    this.isChanged = true;
                }
                this.$emit('input', val);
            }
        },

        data: () => {
            return {
                isChanged: false,
                timezones: [],
                timezone: null,
            }
        },

        mounted:function () {
            this.getTimezones();
        },

        methods: {
            getTimezones(){
                axios.get(configs.apiUrl + "/select/timezones").then((response) => {
                    if(response.data.success === true && response.data.Timezones.length > 0) {
                        this.timezones = response.data.Timezones;
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
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