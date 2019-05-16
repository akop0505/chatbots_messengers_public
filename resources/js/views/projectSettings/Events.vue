<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-navbar type="light">
                    <b-navbar-nav class="ml-auto">
                        <b-button variant="success" @click="newEvent()">
                            Новое событие
                        </b-button>
                    </b-navbar-nav>
                </b-navbar>
                <b-card :header="caption" v-if="items.length >0">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields" >
                        <template slot="project" slot-scope="data">
                            <span v-text="data.item.Project.name"></span>
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
                    <b-alert show variant="warning" class="mb-0">Вы не добавили еще ни одно событие, добавьте событие для начала работы.</b-alert>
                </b-card>
            </b-col>
        </b-row>
        <b-modal id="modalDeleteEvent"
                 ref="modal"
                 title="Предупреждение"
                 @ok="remove"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            Вы действительно хотите удалить данное событие?
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
    import { events } from  '../../testData/data';
    import axios from 'axios';

    export default {
        name: "Events",
        mounted() {
            this.getAll();
        },
        data: () => {
            return {
                warningModalShow:false,
                warningMessage:"",
                caption: "<i class='fa fa-align-justify'></i> События",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                item: {},
                items: [],
                fields: [
                    {key: 'name',       label:  'Название',  sortable: true},
                    {key: 'description',label:  'Описание',  sortable: true},
                    {key: 'project',    label:  'Проект',    sortable: true},
                    {key: 'actions',    label:  '',  sortable: false,class:"action"}
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0,
                modalShow:false
            }
        },
        methods: {
            edit (value) {
                this.$router.push({ name: 'event.form', params:{id:value.id}});
            },
            newEvent () {
                this.$router.push({ name: 'event.form' });
            },
            remove(evt) {
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/events/" + this.item.id).then((response)=>{
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
                        this.warningMessage = 'Данное событие используется на одном из проектов.';
                        this.warningModalShow = !this.warningModalShow;
                    }else{
                        this.item = _.clone(value, true);
                        this.modalShow = !this.modalShow;
                    }
                }
            },
            getAll(){
                axios.get(configs.apiUrl + "/events?page=" + (this.currentPage - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Events;
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