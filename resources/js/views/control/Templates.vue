<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-card-header>
                    <b-row>
                        <b-col sm="4" class="header-title">
                            <i class='fa fa-align-justify'></i> Шаблоны
                        </b-col>
                        <b-col sm="8">
                            <div class="d-flex" v-if="projectsCount">
                                <b-button variant="success" @click="newTemplate()" class="ml-auto">
                                    Новый шаблон
                                </b-button>
                            </div>
                        </b-col>
                    </b-row>
                </b-card-header>
                <b-card v-if="totalRows">
                    <b-table  :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields" :current-page="currentPage" :per-page="perPage">
                        <template slot="country" slot-scope="data">
                            <p v-text="data.item.Language.name"></p>
                        </template>
                        <template slot="project" slot-scope="data">
                            <p v-text="data.item.Project.name"></p>
                        </template>
                        <template slot="event" slot-scope="data">
                            <p v-text="data.item.Event.name"></p>
                        </template>
                        <template slot="receiver" slot-scope="data">
                            <p v-text="data.item.Receiver.name"></p>
                        </template>
                        <template slot="menu" slot-scope="data">
                                <!--<span v-text="data.item.menu.name" :id="'tooltip-' + data.item.menu.id" ></span>-->
                            <b-btn  :id="'tooltip-' + data.item.Menu.id" v-text="data.item.Menu.name" class="menu-btn" variant="link">Button</b-btn>
                                <b-tooltip :target="'tooltip-' + data.item.Menu.id">
                                    <div class="text-left" v-for="(item,key) in data.item.Menu.MenuItems" :key="'menu_'+key">
                                        <span v-text="item.point"></span> <span v-text="item.name"></span>
                                    </div>
                                </b-tooltip>
                        </template>
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                            <i :class="{'icon-trash icons':true, 'text-danger':!data.item.removable}" @click="toggleDeleteModal(data.item)"></i>
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons @input="getAll()"/>
                    </nav>
                </b-card>
                <b-card v-else>
                    <b-alert v-if="projectsCount <= 0" show variant="warning" class="mb-0">У вас нет ни одного проекта, для заведения шаблона вам необходимо добавить один или несколько проектов</b-alert>
                    <b-alert v-else show variant="warning" class="mb-0">Ни к одному проекту на данный момент не создано шаблонов</b-alert>
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
            Вы действительно хотите удалить данный шаблон?
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
    import { templates } from  '../../testData/data';

    export default {
        name: "Templates",
        mounted(){
            this.getAll();
            this.getProjectsCount();
        },
        data: () => {
            return {
                warningMessage : '',
                warningModalShow:false,
                caption: "<i class='fa fa-align-justify'></i> Шаблоны",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                item: {},
                modalShow:false,
                items: [],
                projectsCount:0,
                fields: [
                    // {key: 'id',          label:'ID',                sortable: true, thStyle: { width:"100px" }},
                    {key: 'project',    label:'Проект',     sortable: true},
                    {key: 'country',    label:'Язык',       sortable: true},
                    {key: 'name',       label:'Имя',        sortable: true},
                    {key: 'event',      label:'Событие',    sortable: true},
                    {key: 'receiver',   label:'Получатель', sortable: true},
                    {key: 'text',       label:'Текст',      sortable: true},
                    {key: 'menu',       label:'Меню',       sortable: true},
                    // {key: 'created_at', label:'Создан',     sortable: true},
                    {
                        key: 'actions',
                        label: '',
                        sortable: false,
                        class: "action",
                    }
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0
            }
        },
        methods: {
            toggleDeleteModal(value) {
                if(this.modalShow){
                    this.item = {};
                    this.modalShow = !this.modalShow;
                }
                else if(value !== undefined){
                    if(!value.removable){
                        this.warningMessage = 'Данный шаблон используется на одном из проектов.';
                        this.warningModalShow = !this.warningModalShow;
                    }else{
                        this.item = _.clone(value, true);
                        this.modalShow = !this.modalShow;
                    }
                }
            },
            edit (value) {
                this.$router.push({ name: 'template.form' ,params:{id:value.id}})
            },
            newTemplate () {
                this.$router.push({ name: 'template.form' })
            },
            getAll(){
                axios.get(configs.apiUrl + "/templates?page=" + (this.currentPage - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Templates;
                        this.totalRows = response.data.totalRows
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
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
            remove(){
                axios.delete(configs.apiUrl + "/templates/" + this.item.id).then((response)=>{
                    if(response.data.success === true) {
                        this.getAll();
                        this.items = rows;
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
        }
    }
</script>

<style >
    .header-title{
        padding-top: 10px;
    }
    .icons{
        margin-left: 10px;
        cursor: pointer;
    }
    .action {
        width: 70px !important;
    }
</style>