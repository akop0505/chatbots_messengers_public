<template>
    <div class="animated fadeIn">
        <b-card >
            <b-card-body class="p-0">
                <template v-if="!project_id">
                    <b-row class="alert-block">
                        <b-col sm="12">
                            <b-alert show variant="warning" class="mb-0">Дополнительные настройки доступны только в режиме редактирования.</b-alert>
                        </b-col>
                    </b-row>
                </template>
                <template v-if="project_id">

                    <b-card no-body>
                        <div slot="header">
                            <i class="fa fa-edit pr-2"></i><span>Рабочие часы</span>
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
                                        <week-days v-model="week_days" :errors="errors.weekDays" @save="saveWeekDays"></week-days>
                                    </b-col>

                                    <b-col sm="12">
                                        <timezone v-model="timezone" :errors="errors.timezone" @save="saveTimezone"></timezone>
                                    </b-col>

                                </b-row>
                            </b-card-body>
                        </b-collapse>
                    </b-card>

                    <b-card no-body>
                        <div slot="header">
                            <i class="fa fa-edit pr-2"></i><span>Телефонные номера офиса</span>
                            <div class="card-header-actions">
                                <b-link class="card-header-action btn-minimize" v-b-toggle.collapse2>
                                    <i class="icon-arrow-up"></i>
                                </b-link>
                            </div>
                        </div>

                        <b-collapse id="collapse2" visible>
                            <b-card-body>
                                <office-phones></office-phones>
                            </b-card-body>
                        </b-collapse>
                    </b-card>
                </template>
            </b-card-body>
        </b-card>
    </div>
</template>


<script>
    import { vSwitch, vCase, vDefault } from 'v-switch-case';
    import { ModelListSelect } from 'vue-search-select';
    import WeekDays from "./ModalComponents/WeekDays";
    import Timezone from "./ModalComponents/Timezone";
    import OfficePhones from "./ModalComponents/OfficePhones";

    export default {
        name: "ProjectAdditional",
        components: {
            OfficePhones,
            Timezone,
            WeekDays,
            ModelListSelect,
        },
        mounted(){

            if(this.$route.params && this.$route.params.id !== undefined) {
                this.project_id = this.$route.params.id;
                this.getProject(this.project_id);
                this.getWeekDays();
            }
            else{
                this.project_id = null;
            }
        },
        data: () => {
            return {
                project_id:null,
                item: {},
                items: [],
                timezone: null,
                errors: {
                    weekDays: {},
                    timezone: {},
                },
                week_days: null,
            }
        },
        methods: {
            getProject(id){
                axios.get(configs.apiUrl + "/projects/" + id).then(response=>{
                    if(response.data.success === true) {
                        this.timezone = response.data.Project.timezone;
                    }
                    else {
                        this.item = {};
                    }
                }).catch(e => {
                    //console.error(e);
                })
            },
            getWeekDays(){
                axios.get(configs.apiUrl + "/project/week-days?project_id="+this.project_id).then((response) => {
                    if(response.data.success === true && response.data.WeekDays.length > 0) {
                        this.week_days = response.data.WeekDays;
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
            saveTimezone(){
                let data = {
                    timezone: this.timezone
                };
                axios.post(configs.apiUrl + "/project/timezone/"+this.project_id, data).then((response) => {
                    console.log("Save response  = ",response);
                    if(response.data.success === true) {
                        this.errors.timezone = {};
                    }else{
                    }
                }).catch(e=>{
                    let rError = e.response.data;
                    if(rError.errorType !== undefined && rError.errorType === "VALIDATION_ERROR"){
                        this.errors.timezone = rError.errors;
                        console.log(this.errors);
                    }
                });
            },
            saveWeekDays(){

                let data = Object.assign([], this.week_days);
                data.forEach( (day) => {
                    day['project_id'] = this.project_id
                });
                axios.post(configs.apiUrl + "/project/week-days", data).then((response) => {
                    console.log("Save response  = ",response);
                    if(response.data.success === true) {
                        this.errors.weekDays = {};
                    }else{
                    }
                }).catch(e=>{
                    let rError = e.response.data;
                    if(rError.errorType !== undefined && rError.errorType === "VALIDATION_ERROR"){
                        this.errors.weekDays = rError.errors;
                        console.log(this.errors);
                    }
                });
            },
            edit(value) {
                this.item = {};
                this.errors = [];
                if(value || value !== undefined){
                    this.item = _.clone(value, true);
                    this.item.variable = this.item.variable.replace(/[\{\}]*/gi,"");
                }
                this.toggleFormModal();
            },
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
            updateInactiveStatus(item){
                this.item = _.clone(item, true);
                this.item.variable = this.item.variable.replace(/[\{\}]*/gi,"");
                this.update(false);
            },
            save(evt){
                evt.preventDefault();
                if(this.item.id !== undefined){
                    this.update(true);
                }else{
                    this.store();
                }
            },
            store(){
                this.item.project_id = this.project_id;
                axios.post(configs.apiUrl + "/project/parameters", this.item).then((response) => {
                    console.log("Save response  = ",response);
                    if(response.data.success === true) {
                        this.getAll(this.currentPage);
                        this.toggleFormModal();
                        this.item = {};
                    }else{
                    }
                    this.item = {}
                }).catch(e=>{
                    let rError = e.response.data;
                    if(rError.errorType !== undefined && rError.errorType === "VALIDATION_ERROR"){
                        this.errors = rError.errors;
                        console.log(this.errors);
                    }
                });
            },
            update(update){
                axios.put(configs.apiUrl + "/project/parameters/" + this.item.id,this.item).then((response) => {
                    if(response.data.success === true) {

                        let data = response.data;
                        let index = this.items.findIndex(v => {
                            return this.item.id === v.id;
                        });
                        this.items.splice(index,1,data.Parameter);
                        if(update){
                            this.toggleFormModal();
                        }
                    }else{
                    }
                }).catch(e=>{
                    let rError = e.response.data;
                    if(rError.errorType !== undefined && rError.errorType === "VALIDATION_ERROR"){
                        this.errors = rError.errors;
                    }
                    //console.error(e);
                });
            },
            destroy(evt){
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/project/parameters/" + this.item.id).then((response) => {
                    if(response.data.success === true) {

                        let data = response.data;
                        let index = this.items.findIndex(v => {
                            return this.item.id === v.id;
                        });
                        this.items.splice(index,1);
                        this.toggleDeleteModal();
                    }else{
                    }
                }).catch(e=>{
                    console.log(e.response);
                });
            },
        }
    }
</script>

<style>
    .input-full-width{
        flex: 1 1 auto;
        width: 1%;
    }
    .table.week-days td{
        vertical-align: middle;
    }
    .table.week-days tr:first-child td{
        border-top: 0px;
    }
    .alert-block{
        margin-top: 30px;
    }
    .messengers .fab{
        margin:2px;
    }
    .icons{
        margin-left: 10px;
        cursor: pointer;
    }
    .action {
        width: 70px !important;
    }
</style>