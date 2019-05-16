<template>
    <div class="animated fadeIn">
        <b-card no-body>
            <div slot="header">
                <i class="fa fa-edit"></i><span v-text="formTitle"></span>
                <div class="card-header-actions">
                    <b-link class="card-header-action btn-minimize" v-b-toggle.collapse1>
                        <i class="icon-arrow-up"></i>
                    </b-link>
                </div>
            </div>
            <b-collapse id="collapse1" visible>
                <b-card-body>
                    <b-row>
                        <b-col sm="12">
                            <form @submit.stop.prevent="handleSave">
                                <b-row>
                                    <b-col sm="12">
                                        <b-form-group :state="validateState('name')"
                                                      aria-describedby="projectLiveFeedback">
                                            <label>Проект</label>
                                            <model-list-select
                                                    :class="{'form-control':true, 'is-invalid': validateState('project_id') === false,'search-select-invalid':validateState('project_id') === false}"
                                                    :list="projects"
                                                    v-model="item.project_id"
                                                    option-value="id"
                                                    option-text="name"
                                                    :selected-option="item.project_id"
                                                    placeholder="Выберите проект">
                                            </model-list-select>
                                            <b-form-invalid-feedback id="projectLiveFeedback" >
                                                <template v-if="validateState('project_id') === false " v-for="nErr in errors.project_id" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-form-group>
                                            <label for="nameInputId">Название</label>
                                            <b-form-input type="text" id="nameInputId" placeholder="Название" v-model="item.name"
                                                          :state="validateState('name')"
                                                          aria-describedby="nameLiveFeedback"
                                            ></b-form-input>

                                            <b-form-invalid-feedback id="nameLiveFeedback" >
                                                <template v-if="validateState('name') === false " v-for="nErr in errors.name" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-form-group>
                                            <label for="descriptionInputId">Описание</label>
                                            <b-form-textarea
                                                    :state="validateState('description')"
                                                    aria-describedby="descriptionLiveFeedback"
                                                    id="descriptionInputId"
                                                    placeholder="Описание получателя"
                                                    v-model="item.description"
                                                    :rows="3"
                                                    :max-rows="6"
                                            ></b-form-textarea>
                                            <b-form-invalid-feedback id="descriptionLiveFeedback" >
                                                <template v-if="errors.description && errors.description.length" v-for="nErr in errors.description" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <div class="d-flex">
                                            <b-button class="btn btn-danger ml-auto" @click="handleCancel">Отмена</b-button>
                                            <b-button class="btn btn-success success-btn"   @click="handleSave">Сохранить</b-button>
                                        </div>
                                    </b-col>
                                </b-row>

                            </form>
                        </b-col>
                    </b-row>
                </b-card-body>
            </b-collapse>
        </b-card>
    </div>
</template>


<script>
    import { receivers } from  '../../testData/data';
    import { ModelListSelect } from 'vue-search-select';

    export default {
        name: "ReceiversForm",
        components: {
            ModelListSelect
        },
        mounted() {
            this.loadProjects();
            if(this.$route.params && this.$route.params.id !== undefined) {
                this.formTitle = "Редактирование";
                this.getItem(this.$route.params.id);
            }else {
                this.formTitle = "Новый получатель";
            }
        },
        data: () => {
            return {
                formTitle: "Новый получатель",
                projects:[],
                item: {},
                errors:{}
            }
        },
        methods: {
            handleSave (evt) {
                evt.preventDefault();
                if(this.$route.params && this.$route.params.id === undefined) {
                    this.newItem();
                }
                else{
                    this.updateItem(this.$route.params.id)
                }
            },
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
            loadProjects() {
                axios.get(configs.apiUrl + "/select/projects/").then(response=>{
                    if(response.data.success === true){
                        this.projects = response.data.Projects;
                    }
                    else{
                        this.projects = {};
                    }
                }).catch(e => {
                    //console.log(e);
                });
            },
            getItem(id) {
                axios.get(configs.apiUrl + "/receivers/" + id).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Receiver;
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error(e);
                });
            },
            newItem(){
                axios.post(configs.apiUrl + "/receivers", this.item).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.receivers;
                        this.$router.push({ name: 'receiver' });
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error("error = ",e);
                    this.errors = e.response.data.errors;
                });
            },
            updateItem(id){
                axios.put(configs.apiUrl + "/receivers/" + id, this.item).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Receivers;
                        this.$router.push({ name: 'receiver' });
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.log("error Response = ",e.response);
                    this.errors = e.response.data.errors;
                });
            },
            handleCancel (evt) {
                evt.preventDefault();
                this.$router.go(-1);
            }
        }
    }
</script>

<style scoped>
    .success-btn {
        margin-left: 10px;
    }
    .search-select-invalid{
        border-color: #f86c6b !important;
    }
</style>