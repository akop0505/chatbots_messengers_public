<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-navbar type="light" >
                    <b-navbar-nav class="ml-auto">
                        <b-button variant="success" @click="newRole()">
                            Новая роль
                        </b-button>
                    </b-navbar-nav>
                </b-navbar>
                <b-card :header="caption">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields" >
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                            <i :class="{'icon-trash icons':true, 'text-danger':!data.item.removable}" @click="deleteItem(data.item)"></i>
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons @input="getAll(currentPage)"/>
                    </nav>
                </b-card>
            </b-col>
        </b-row>
        <b-modal id="modalDeleteProject"
                 ref="modal"
                 title="Предупреждение"
                 @ok="remove"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            Вы действительно хотите удалить данную роль?
        </b-modal>
        <warning-component ref="warningModal"></warning-component>
    </div>
</template>


<script>
    import { roles } from  '../../testData/data';
    import axios from 'axios';
    import WarningComponent from "../modals/WarningComponent";

    export default {
        name: "Roles",
        components:{
            WarningComponent
        },
        data: () => {
            return {
                modalShow: false,
                caption: "<i class='fa fa-align-justify'></i> Роли",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:false,
                item: {},
                items: [],
                fields: [
                    {key: 'name',       label:'Название',           sortable: true},
                    {key: 'description',label:'Описание',           sortable: true},
                    // {key: 'created_at', label:'Создан',             sortable: true},
                    {key: 'actions',    label: '',          sortable: false, class:"action"}
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0
            }
        },
        mounted(){
            this.getAll();
        },
        methods: {
            getRowCount (items) {
                return items.length
            },
            clearItem () {
                this.item = {}
            },
            edit (value) {
                this.$router.push({ path:'roles/form/'+value.id });
            },
            remove(evt) {
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/roles/" + this.item.id).then((response)=>{
                    if(response.data.success){
                        this.modalShow = !this.modalShow;
                        this.getAll(this.currentPage);
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
            newRole () {
                this.$router.push({ path:'roles/form'});
            },
            deleteItem(value) {
                if(this.modalShow){
                    this.item = {};
                    this.modalShow = !this.modalShow;
                }
                else if(value !== undefined){
                    if(!value.removable){
                        this.$refs.warningModal.toggle('Удаление данного роля недоступно.');
                    }else{
                        this.item = _.clone(value, true);
                        this.modalShow = !this.modalShow;
                    }
                }
            },
            getAll(current){
                if(current === undefined){
                    current = 1;
                }
                axios.get(configs.apiUrl + "/roles?page=" + (current - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Roles;
                        this.totalRows = response.data.totalRows
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            }
        }
    }
</script>

<style >
    .icons{
        margin-left: 10px;
        cursor: pointer;
    }
    .action {
        width: 70px !important;
    }
</style>