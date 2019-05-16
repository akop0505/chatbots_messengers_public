<template>
    <div class="animated fadeIn">
        <b-card no-body>

            <div v-if="newCreated">
                <b-alert :show="5" variant="success" >Новый лид успешно создан.</b-alert>
            </div>


                <b-card-body>

                    <form @submit.stop.prevent="handleSave">

                    <b-card no-body>
                        <div slot="header">
                            <i class="fa fa-edit pr-2"></i><span>Параметры</span>
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
                                            <b-row>
                                                <b-col sm="4">
                                                    <b-form-group :state="validateState('project_id')"
                                                                  aria-describedby="project_idLiveFeedback">
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
                                                        <b-form-invalid-feedback id="project_idLiveFeedback" >
                                                            <template v-if="validateState('project_id') === false " v-for="nErr in errors.project_id" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="4">
                                                    <b-form-group :state="validateState('owner_id')"
                                                                  aria-describedby="owner_idLiveFeedback">
                                                        <label>Менеджер</label>
                                                        <model-list-select
                                                                :class="{'form-control':true, 'is-invalid': validateState('owner_id') === false,'search-select-invalid':validateState('owner_id') === false}"
                                                                :list="managers"
                                                                v-model="item.owner_id"
                                                                option-value="id"
                                                                :custom-text="selectFullName"
                                                                :selected-option="item.owner_id"
                                                                placeholder="Выберите менеджера">
                                                        </model-list-select>
                                                        <b-form-invalid-feedback id="owner_idLiveFeedback" >
                                                            <template v-if="validateState('owner_id') === false " v-for="nErr in errors.owner_id" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="4">
                                                    <b-form-group :state="validateState('status')"
                                                                  aria-describedby="statusLiveFeedback">
                                                        <label>Статус</label>
                                                        <model-list-select
                                                                :class="{'form-control':true, 'is-invalid': validateState('status') === false,'search-select-invalid':validateState('status') === false}"
                                                                :list="statuses"
                                                                v-model="item.status"
                                                                option-value="id"
                                                                option-text="name"
                                                                :selected-option="item.status"
                                                                placeholder="Выберите статус">
                                                        </model-list-select>
                                                        <b-form-invalid-feedback id="statusLiveFeedback" >
                                                            <template v-if="validateState('status') === false " v-for="nErr in errors.status" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="4">
                                                    <b-form-group>
                                                        <label for="last_nameInputId">Фамилия</label>
                                                        <b-form-input :state="validateState('last_name')" aria-describedby="last_nameLiveFeedback" type="text" id="last_nameInputId" placeholder="Васильев" v-model="item.last_name" ></b-form-input>
                                                        <b-form-invalid-feedback id="last_nameLiveFeedback" >
                                                            <template v-if="validateState('last_name') === false" v-for="nErr in errors.last_name" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="4">
                                                    <b-form-group>
                                                        <label for="first_nameInputId">Имя</label>
                                                        <b-form-input :state="validateState('first_name')" aria-describedby="first_nameLiveFeedback" type="text" id="first_nameInputId" placeholder="Игорь" v-model="item.first_name" ></b-form-input>
                                                        <b-form-invalid-feedback id="first_nameLiveFeedback" >
                                                            <template v-if="validateState('first_name') === false" v-for="nErr in errors.first_name" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="4">
                                                    <b-form-group>
                                                        <label for="father_nameInputId">Отчество</label>
                                                        <b-form-input :state="validateState('father_name')" aria-describedby="father_nameLiveFeedback" type="text" id="father_nameInputId" placeholder="Николаевич" v-model="item.father_name" ></b-form-input>
                                                        <b-form-invalid-feedback id="father_nameLiveFeedback" >
                                                            <template v-if="validateState('father_name') === false" v-for="nErr in errors.father_name" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="6">
                                                    <b-form-group>
                                                        <label for="emailInputId">Email</label>
                                                        <b-form-input :state="validateState('email')" aria-describedby="emailLiveFeedback" type="text" id="emailInputId" placeholder="example@ex.ru" v-model="item.email"></b-form-input>
                                                        <b-form-invalid-feedback id="emailLiveFeedback" >
                                                            <template v-if="validateState('email') === false " v-for="nErr in errors.email">
                                                                <span v-if="typeof nErr === 'object'" v-for="n in nErr" >{{n}}<br></span>
                                                                <span v-if="typeof nErr === 'string'" v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="6">
                                                    <b-form-group>
                                                        <label for="phoneInputId">Телефон</label>
                                                        <b-form-input :state="validateState('phone')" @focus="setPhonePlus()" :maxlength="13" aria-describedby="phoneLiveFeedback" type="text" id="phoneInputId" placeholder="+79099876543" v-model="item.phone"></b-form-input>
                                                        <b-form-invalid-feedback id="phoneLiveFeedback">
                                                            <template v-if="validateState('phone') === false " v-for="nErr in errors.phone">
                                                                <span v-if="typeof nErr === 'object'" v-for="n in nErr" >{{n}}<br></span>
                                                                <span v-if="typeof nErr === 'string'" v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                            </b-row>
                                        </b-col>
                                    </b-row>

                                    <b-row>
                                        <b-col sm="12">
                                            <div class="d-flex">
                                                <b-button class="btn btn-danger ml-auto" @click="handleCancel">Отмена</b-button>
                                                <b-button class="btn btn-success success-btn"   @click="handleSave">Сохранить</b-button>
                                            </div>
                                        </b-col>
                                    </b-row>
                            </b-card-body>
                        </b-collapse>
                    </b-card>

                    </form>

                </b-card-body>

        </b-card>
    </div>
</template>


<script>
    import { vSwitch, vCase, vDefault } from 'v-switch-case';
    import axios from 'axios';
    import { ModelListSelect } from 'vue-search-select';
    import Button from "bootstrap-vue/es/components/button/button";

    export default {
        name: "LeadsForm",
        components: {
            Button,
            ModelListSelect,
        },
        directives: {
            'switch': vSwitch,
            'case': vCase,
            'default': vDefault
        },
        updated(){
            if(this.$route.params && this.$route.params.newCreated){
                this.newCreated = true;
                delete this.$route.params.newCreated;
                this.$router.replace({ params :this.$route.params });
            }
        },
        mounted() {
            this.loadProjects();
            this.loadManagers();
            this.loadStatuses();

            if(this.$route.params && this.$route.params.id !== undefined) {

                this.formTitle = "Редактирование";
                this.getLead(this.$route.params.id);
                this.action = "update";
            }else {
                this.formTitle = "Новый лид";
                this.action = "create";
                this.item = {
                    last_name: "",
                    first_name: "",
                    father_name: "",
                    phone: "",
                    email: "",
                    owner_id: null,
                    project_id: null,
                    status: null,
                }
            }
        },
        data: () => {
            return {
                newCreated:false,
                modalRemoveKeyShow:false,
                action:"create",
                errors:{},
                formTitle: "Новый лид",
                item: {},
                statuses: [],
                managers:[],
                projects:[],
            }
        },
        methods: {
            setPhonePlus(){
                if(!this.item.phone){
                    this.item.phone = "+";
                }
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
                    console.log(e);
                });
            },
            loadManagers() {
                axios.get(configs.apiUrl + "/select/managers/").then(response=>{
                    if(response.data.success === true){
                        this.managers = response.data.Managers;
                    }
                    else{
                        this.managers = {};
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            loadStatuses() {
                axios.get(configs.apiUrl + "/select/lead-statuses/").then(response=>{
                    if(response.data.success === true){
                        this.statuses = response.data.Statuses;
                    }
                    else{
                        this.statuses = {};
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            selectFullName (v) {
                return `${v.last_name} ${v.first_name} ${v.father_name || ''}`
            },
            handleOk(evt) {
                evt.preventDefault();
            },
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
            handleSubmit () {
                this.$refs.modal.hide()
            },
            handleSave (evt) {
                evt.preventDefault();
                if(this.$route.params && this.$route.params.id !== undefined) {
                    this.updateLead(this.item.id);
                }else{
                    this.newLead();
                }
                //this.$router.push({ path: '/ai-system/projects' });
            },
            handleCancel (evt) {
                evt.preventDefault();
                this.$router.go(-1);
            },
            getLead(id){
                axios.get(configs.apiUrl + "/leads/" + id).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Lead;
                        if( ! this.item.options){
                            this.item.options = {
                                tasks: []
                            };
                        }
                    }
                    else {
                        this.item = {};
                    }
                }).catch(e => {
                    //console.error(e);
                })
            },
            newLead(){
                let dataToSave = Object.assign({},this.item);
                axios.post(configs.apiUrl + "/leads",dataToSave).then(response=>{
                    if(response.data.success === true){
                        this.errors = [];
                        this.item.id = response.data.Lead.id;
                        this.action = "update";
                        this.formTitle = "Редактирование";
                        this.$router.push({ name: 'leads.form',params:{id:this.item.id,newCreated:true} });
                    }else{

                    }
                }).catch(e => {
                    let { data } = e.response;
                    if(data.errorType !== undefined && data.errorType === "VALIDATION_ERROR")  {
                       this.errors = data.errors;
                    }else{
                        //console.error(data.errors);
                    }
                })
            },
            updateLead(id){
                let dataToSave = Object.assign({},this.item);
                axios.put(configs.apiUrl + "/leads/" + id,dataToSave).then(response=>{
                    if(response.data.success === true){
                        this.item = response.data.Lead;
                        this.$router.push({ name: 'leads' });
                    }else{

                    }
                }).catch(e => {
                    let { data } = e.response;
                    if(data.errorType !== undefined && data.errorType === "VALIDATION_ERROR")  {
                        this.errors = data.errors;
                    }
                    else{
                        //console.error(data.errors);
                    }
                })
            },
        }
    }
</script>

<style scoped>
    .success-btn {
        margin-left: 10px;
    }
    .messenger-input-group-prepend{
        width: 50px;
        padding-top:5px;
    }
    .btn-new-messenger {
        cursor:pointer;
        position: absolute;
        padding-top: 5px;
        padding-left: 5px
    }
    .btn-remove-messenger{
        position: absolute;
        margin-top: 37px;
    }
    .keys_block{
        margin-bottom: 20px;
    }
    .search-select-invalid{
        border-color: #f86c6b !important;
    }
    .custom-icon{
        margin-top: 30px;
        padding-left: 70px;
    }
</style>