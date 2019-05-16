<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-navbar type="light">
                    <b-navbar-nav class="ml-auto">
                        <b-button variant="success" @click="edit()">
                            Добавить номер
                        </b-button>
                    </b-navbar-nav>
                </b-navbar>
                <div v-if="items.length > 0">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields">
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                            <i class="icon-trash icons " @click="toggleDeleteModal(data.item)"></i>
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons @input="getAll(currentPage)"/>
                    </nav>
                </div>
                <div v-else>
                    <b-alert show variant="warning" class="mb-0">Вы не добавили ни один номер к этому проекту, добавьте номер для начала работы.</b-alert>
                </div>
            </b-col>
        </b-row>
        <b-modal id="modalForm"
                 ref="modal"
                 :title=" this.item.id ? 'Изменить номер' :'Добавить номер'"
                 @ok="save"
                 ok-title="Сохранить"
                 cancel-title="Отмена"
                 v-model="modalFormShow"
        >
            <b-form-group>
                <label for="phoneInputId">Номер телефона</label>
                <b-form-input :state="validateState('phone')" aria-describedby="phoneLiveFeedback" type="text" id="phoneInputId" placeholder="+7 (999) 222-22-22" v-model="item.phone" ></b-form-input>
                <b-form-invalid-feedback id="phoneLiveFeedback" >
                    <template v-if="validateState('phone') === false" v-for="nErr in errors.phone" >
                        <span  v-text="nErr"></span><br>
                    </template>
                </b-form-invalid-feedback>
            </b-form-group>
            <b-form-group>
                <label for="extraInputId">Добавочный</label>
                <b-form-input :state="validateState('extra')" aria-describedby="extraLiveFeedback" type="text" id="extraInputId" placeholder="Добавочный код" v-model="item.extra" ></b-form-input>
                <b-form-invalid-feedback id="extraLiveFeedback" >
                    <template v-if="validateState('extra') === false" v-for="nErr in errors.extra" >
                        <span  v-text="nErr"></span><br>
                    </template>
                </b-form-invalid-feedback>
            </b-form-group>
        </b-modal>
        <b-modal id="modalDeletePhone"
                 ref="modal"
                 title="Предупреждение"
                 @ok="destroy"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalDeleteShow"
        >
            Вы действительно хотите удалить данный номер?
        </b-modal>
    </div>
</template>

<script>
    export default {
        name: "office-phones",
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
                    {key: 'phone',   label:  'Телефон',       sortable: true},
                    {key: 'extra',       label:  'Добавочный',  sortable: true},
                    {key: 'created_at', label:  'Добавлен',            sortable: true},
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
                    //this.item.phone = this.item.phone.replace(/[\{\}]*/gi,"");
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
                //this.item.phone = this.item.phone.replace(/[\{\}]*/gi,"");
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
                axios.post(configs.apiUrl + "/project/office-phones", this.item).then((response) => {
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
                axios.put(configs.apiUrl + "/project/office-phones/" + this.item.id,this.item).then((response) => {
                    if(response.data.success === true) {

                        let data = response.data;
                        let index = this.items.findIndex(v => {
                            return this.item.id === v.id;
                        });
                        this.items.splice(index,1,data.ProjectOfficePhone);
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
                axios.delete(configs.apiUrl + "/project/office-phones/" + this.item.id).then((response) => {
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
                axios.get(configs.apiUrl + "/project/office-phones?project_id="+ this.project_id +"&page=" + (current - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.ProjectOfficePhones;
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