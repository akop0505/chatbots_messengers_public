<template>
    <div class="animated fadeIn">
        <template v-if="!project_id">
            <b-row class="alert-block">
                <b-col sm="12">
                    <b-alert show variant="warning" class="mb-0">Добавление переменных возможно только в режиме редактирования.</b-alert>
                </b-col>
            </b-row>
        </template>
        <template v-if="project_id">
            <b-row>
                <b-col sm="12">
                    <b-navbar type="light">
                        <b-navbar-nav class="ml-auto">
                            <b-button variant="success" @click="edit()">
                                Добавить переменную
                            </b-button>
                        </b-navbar-nav>
                    </b-navbar>
                    <b-card :header="caption" v-if="items.length > 0">
                        <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields">
                            <template slot="inactive" slot-scope="data">
                                <b-form-checkbox-group stacked id="inactiveInput">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" :id="'inactiveInputId' + data.item.id" v-model="data.item.inactive" @change="updateInactiveStatus(data.item)">
                                        <label class="custom-control-label" :for="'inactiveInputId' + data.item.id">отключить</label>
                                    </div>
                                </b-form-checkbox-group>
                            </template>
                            <template slot="actions" slot-scope="data">
                                <i class="icon-pencil icons " @click="edit(data.item)"></i>
                                <i class="icon-trash icons " @click="toggleDeleteModal(data.item)"></i>
                            </template>
                        </b-table>
                        <nav>
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons @input="getAll(currentPage)"/>
                        </nav>
                    </b-card>
                    <b-card v-else>
                        <b-alert show variant="warning" class="mb-0">Вы не добавили ни одну переменную к этому проекту, добавьте переменные для начала работы.</b-alert>
                    </b-card>
                </b-col>
            </b-row>
        </template>
        <b-modal id="modalForm"
                 ref="modal"
                 :title=" this.item.id ? 'Изменить переменную' :'Добавить переменную'"
                 @ok="save"
                 ok-title="Сохранить"
                 cancel-title="Отмена"
                 v-model="modalFormShow"
        >
            <b-form-group>
                <label for="variableInputId">Код переменной</label>
                <b-form-input :state="validateState('variable')" aria-describedby="variableLiveFeedback" type="text" id="variableInputId" placeholder="OBJECT_ID" v-model="item.variable" ></b-form-input>
                <b-form-invalid-feedback id="variableLiveFeedback" >
                    <template v-if="validateState('variable') === false" v-for="nErr in errors.variable" >
                        <span  v-text="nErr"></span><br>
                    </template>
                </b-form-invalid-feedback>
            </b-form-group>
            <b-form-group>
                <label for="nameInputId">Название переменной</label>
                <b-form-input :state="validateState('name')" aria-describedby="nameLiveFeedback" type="text" id="nameInputId" placeholder="Пояснение к переменной" v-model="item.name" ></b-form-input>
                <b-form-invalid-feedback id="nameLiveFeedback" >
                    <template v-if="validateState('name') === false" v-for="nErr in errors.name" >
                        <span  v-text="nErr"></span><br>
                    </template>
                </b-form-invalid-feedback>
            </b-form-group>
        </b-modal>
        <b-modal id="modalDeleteProject"
                 ref="modal"
                 title="Предупреждение"
                 @ok="destroy"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalDeleteShow"
        >
            Вы действительно хотите удалить данную переменную?
        </b-modal>
    </div>
</template>


<script>
    import { vSwitch, vCase, vDefault } from 'v-switch-case';
    import { projectVariables } from '../../testData/data';

    export default {
        name: "ProjectVariables",
        mounted(){
            if(this.$route.params && this.$route.params.id !== undefined) {
                this.project_id = this.$route.params.id;
                this.getAll();
            }
            else{
                this.project_id = null;
            }
        },
        data: () => {
            return {
                project_id:null,
                modalDeleteShow: false,
                modalFormShow:false,
                caption: "<i class='fa fa-align-justify'></i> Переменный проекта",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                item: {},
                items: [],
                errors: [],
                fields: [
                    {key: 'variable',   label:  'Код переменной',       sortable: true},
                    {key: 'name',       label:  'Название переменной',  sortable: true},
                    {key: 'created_at', label:  'Добавлена',            sortable: true},
                    {key: 'inactive',   label:  'Статус',            sortable: true},
                    {key: 'actions',    label:  '',             sortable: false, class:'action'},
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0
            }
        },
        methods: {
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
            toggleFormModal(){
                this.modalFormShow = !this.modalFormShow;
            },
            toggleDeleteModal(value) {
                if(this.modalDeleteShow){
                    this.item = {};
                }
                else if(value !== undefined){
                    this.item = _.clone(value, true);
                }
                this.modalDeleteShow = !this.modalDeleteShow;
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
            getAll(current) {
                if(current === undefined){
                    current = 1;
                }
                axios.get(configs.apiUrl + "/project/parameters?project_id="+ this.project_id +"&page=" + (current - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Parameters;
                        this.totalRows = response.data.totalRows
                    }else{
                        this.items = [];
                        this.totalRows = 0;
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            }
        }
    }
</script>

<style >
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