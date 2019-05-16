<template>
    <div>
        <b-form-group :state="validateState('template_id')"
                      aria-describedby="templateLiveFeedback">
            <label>Сообщение</label>
            <model-list-select
                    :class="{'form-control':true, 'is-invalid': validateState('template_id') === false,'search-select-invalid':validateState('template_id') === false}"
                    :list="templates"
                    v-model="value.template_id"
                    option-value="id"
                    option-text="name"
                    :selected-option="value.template_id"
                    placeholder="Выберите шаблон">
            </model-list-select>
            <b-form-invalid-feedback id="templateLiveFeedback" >
                <template v-if="validateState('template_id') === false " v-for="nErr in errors.template_id" >
                    <span  v-text="nErr"></span><br>
                </template>
            </b-form-invalid-feedback>
        </b-form-group>
    </div>
</template>

<script>

    import axios from 'axios';
    import { ModelListSelect } from 'vue-search-select';

    export default {
        components: {ModelListSelect},
        name:       "sms",
        props:      {
            'projectId': {},
            'value': {
                type: Object,
                required: true,
            },
        },
        data: function () {
            return {
                errors: [],
                templates:[],
            }
        },
        mounted() {
            this.loadTemplates();
        },
        methods:    {
            loadTemplates() {
                axios.get(configs.apiUrl + "/select/templates/?project_id="+this.projectId).then(response=>{
                    if(response.data.success === true){
                        this.templates = response.data.Templates;
                    }else{
                        this.templates = {};
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            validate(){
                return new Promise((resolve, reject) => {
                    let dataToSave = Object.assign({},this.value);
                    axios.post(configs.apiUrl + "/sequences/validate-task-options/sms", dataToSave).then(response=>{
                        if(response.data.success === true){
                            this.errors = [];
                            resolve(response);
                        }
                    }).catch(e => {
                        let { data } = e.response;
                        if(data.errorType !== undefined && data.errorType === "VALIDATION_ERROR")  {
                            this.errors = data.errors;
                        }
                        else{
                            //console.error(data.errors);
                        }
                        reject(e)
                    });
                });
            },
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
        }
    }
</script>

<style scoped>

</style>