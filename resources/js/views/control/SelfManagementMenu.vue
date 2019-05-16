<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-card-header>
                    <b-row>
                        <b-col sm="4" class="header-title">
                            <i class='fa fa-align-justify'></i> Меню
                        </b-col>
                        <b-col sm="8">
                            <div class="d-flex" v-if="projectsCount">
                                <b-button variant="success" @click="newTemplate()" class="ml-auto">
                                    Новое меню
                                </b-button>
                            </div>
                        </b-col>
                    </b-row>
                </b-card-header>
                <b-card v-if="totalRows">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields" :current-page="currentPage" :per-page="perPage">
                        <template slot="callback_url"  slot-scope="data">
                            <span><b-link href="javascript:void(0)" v-text="data.item.callback_url"></b-link></span>
                        </template>
                        <template slot="project"  slot-scope="data">
                            <span v-text="data.item.project? data.item.project.name: ''"></span>
                        </template>
                        <template slot="menu_items" slot-scope="data">
                            <b-btn  :id="'tooltip-' + data.item.id"  class="menu-btn" variant="link">Варианты</b-btn>
                            <b-tooltip :target="'tooltip-' + data.item.id">
                                <div class="text-left" v-for="(item,key) in data.item.menu_items" :key="'menu_'+key">
                                    <span v-text="item.point"></span> <span v-text="item.name"></span>
                                    <p>
                                        <span>Функция обратного вызова: </span>
                                        <span><b-link href="javascript:void(0)" v-text="item.callback_url"></b-link></span>
                                    </p>
                                </div>
                            </b-tooltip>
                        </template>
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                            <i :class="{'icon-trash icons':true, 'text-danger':!data.item.removable}" @click="toggleDeleteModal(data.item)"></i>
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons/>
                    </nav>
                </b-card>
                <b-card v-else>
                    <b-alert v-if="projectsCount <= 0" show variant="warning" class="mb-0">У вас нет ни одного проекта, для заведения меню вам необходимо добавить один или несколько проектов</b-alert>
                    <b-alert v-else show variant="warning" class="mb-0">Ни к одному проекту на данный момент не создано меню</b-alert>
                </b-card>
            </b-col>
        </b-row>
        <b-modal id="modalDeleteMenu"
                 ref="modal"
                 title="Предупреждение"
                 @ok="remove"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            Вы действительно хотите удалить данное меню?
        </b-modal>
        <b-modal centered id="warning"
                 ref="modalWarning"
                 header-bg-variant="warning"
                 header-text-variant="dark"
                 title="Предупреждение"
                 ok-title="OK"
                 v-model="warningModalShow"
        >
            <b-container fluid>
                <span class="text-dark">{{ this.warningMessage}}</span>
            </b-container>
            <div slot="modal-footer" class="w-100">
                <b-btn class="float-right" variant="primary" @click="warningModalShow=!warningModalShow">
                    OK
                </b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import { ModelListSelect } from 'vue-search-select';


    export default {
        name: "SelfManagementMenu",
        components: {
            ModelListSelect
        },
        mounted(){
            this.getAll();
            this.getProjectsCount();
        },
        data: () => {
            return {
                warningMessage : '',
                warningModalShow:false,
                caption: "<i class='fa fa-align-justify'></i> Пользователи",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                projectsCount:0,
                item: {username:''},
                items: [],
                fields: [
                    // {key: 'id',          label:'ID',                sortable: true, thStyle: { width:"100px" }},
                    {key: 'name',           label:'Имя',                      sortable: true},
                    {key: 'callback_url',   label:'Функция обратного вызова', sortable: true},
                    {key: 'menu_items',     label:'Варианты'},
                    {key: 'project',        label:'Проект'},
                    {
                        key: 'actions',
                        label: '',
                        sortable: false,
                        class:"action"
                    }
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0,
                modalShow: false
            }
        },
        methods: {
            edit (value) {
                this.$router.push({ name: 'self-management-menu.form', params:{ id: value.id }})
            },
            newTemplate () {
                this.$router.push({ name: 'self-management-menu.form' })
            },
            remove(evt) {
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/menu/" + this.item.id).then((response)=>{
                    if(response.data.success){
                        this.getAll();
                        this.toggleDeleteModal();
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
            toggleDeleteModal(value) {
                if(this.modalShow){
                    this.item = {};
                    this.modalShow = !this.modalShow;
                }
                else if(value !== undefined){
                    if(!value.removable){
                        this.warningMessage = 'Данное меню используется на одном из проектов.';
                        this.warningModalShow = !this.warningModalShow;
                    }else{
                        this.item = _.clone(value, true);
                        this.modalShow = !this.modalShow;
                    }
                }
            },
            getProjectsCount(){
                axios.get(configs.apiUrl + "/projects-count").then((response)=>{
                    if(response.data.success === true) {
                        this.projectsCount = response.data.count
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
            getAll() {
                axios.get(configs.apiUrl + "/menu?page=" + (this.currentPage - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Menus;
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
    .header-title{
        padding-top:10px;
    }
    .icons{
        margin-left: 10px;
        cursor: pointer;
    }
    .action {
        width: 70px !important;
    }
</style>