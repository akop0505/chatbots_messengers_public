<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-navbar type="light" >
                    <b-navbar-nav class="ml-auto">
                        <b-button variant="success" @click="newProject()">
                            Новый проект
                        </b-button>
                    </b-navbar-nav>
                </b-navbar>
                <b-card :header="caption" v-if="items.length>0">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields">
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                            <i :class="{'icon-trash icons':true, 'text-danger':!data.item.removable}" @click="toggleDeleteModal(data.item)"></i>
                        </template>
                        <template slot="web_site" slot-scope="data">
                            <b-link :href="data.item.web_site" target="_blank" v-text="data.item.web_site"></b-link>
                        </template>
                        <template slot="client" slot-scope="data">
                            <p >{{ data.item.client | fullName }}</p>
                        </template>
                        <template slot="messengers" slot-scope="data">
                           <span v-for="val in data.item.project_messengers"  class="messengers">
                               <i v-if="val.gateway.messenger.name == 'Viber'"     class="fab fa-viber fa-2x"      v-b-tooltip.hover :title="val.gateway != null ? 'Шлюз: '+ val.gateway.name : val.bot_name"></i>
                                <i v-if="val.gateway.messenger.name == 'VK'"       class="fab fa-vk fa-2x"         v-b-tooltip.hover :title="val.gateway != null ? 'Шлюз: '+ val.gateway.name : val.bot_name"></i>
                                <i v-if="val.gateway.messenger.name == 'WhatsApp'" class="fab fa-whatsapp fa-2x"   v-b-tooltip.hover :title="val.gateway != null ? 'Шлюз: '+ val.gateway.name : val.bot_name"></i>
                                <i v-if="val.gateway.messenger.name == 'Telegram'" class="fab fa-telegram fa-2x"   v-b-tooltip.hover :title="val.gateway != null ? 'Шлюз: '+ val.gateway.name : val.bot_name"></i>
                                <i v-if="val.gateway.messenger.name == 'SMS'"      class="far fa-comment fa-2x"    v-b-tooltip.hover :title="val.gateway != null ? 'Шлюз: '+ val.gateway.name : val.bot_name"></i>
                                <i v-if="val.gateway.messenger.name == 'Email'"    class="far fa-envelope fa-2x"   v-b-tooltip.hover :title="val.gateway != null ? 'Шлюз: '+ val.gateway.name : val.bot_name"></i>
                                <i v-if="val.gateway.messenger.alias == 'fb'"       class="fab fa-facebook-messenger fa-2x"   v-b-tooltip.hover :title="val.gateway != null ? 'Шлюз: '+ val.gateway.name : val.bot_name"></i>
                            </span>
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons @input="getAll(currentPage)"/>
                    </nav>
                </b-card>
                <b-card v-else>
                    <b-alert show variant="warning" class="mb-0">Вы не добавили еще ни одного проекта, добавьте проект для начала работы.</b-alert>
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
            Вы действительно хотите удалить данный проект?
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
    import { vSwitch, vCase, vDefault } from 'v-switch-case';
    import { projects } from '../../testData/data';

    export default {
        name: "ProjectSettings",
        directives: {
            'switch': vSwitch,
            'case': vCase,
            'default': vDefault
        },
        mounted(){
            this.getAll();
        },
        filters: {
            fullName(value) {
                let name = "";
                if(!value){
                    return name;
                }
                name += value.last_name + " " +value.first_name;
                if(value.father_name){
                    name +=value.father_name;
                }
                return name;
            }
        },
        data: () => {
            return {
                modalShow: false,
                warningModalShow: false,
                warningMessage: "",
                caption: "<i class='fa fa-align-justify'></i> Проекты",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                item: {},
                items: [],
                fields: [
                    {key: 'name',       label:  'Название ',    sortable: true},
                    {key: 'web_site',   label:  'Web сайт',     sortable: true},
                    {key: 'messengers', label:  'Каналы',       sortable: true},
                    {key: 'client',     label:  'Клиент',       sortable: true},
                    {key: 'actions',    label:  '',             sortable: false, class:'action'},
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0
            }
        },
        methods: {
            getRowCount (items) {
                return items.length
            },
            edit (value) {
                this.item = _.clone(value, true);
                this.$router.push({ name: 'project.form',params: { id: value.id  }});
            },
            newProject(){
                this.$router.push({ name: 'project.form' });
            },
            getAll(current) {
                if(current === undefined){
                    current = 1;
                }
                axios.get(configs.apiUrl + "/projects?page=" + (current - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Projects;
                        this.totalRows = response.data.totalRows
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
                        this.warningMessage = 'Удаление данного проекта недоступно.';
                        this.warningModalShow = !this.warningModalShow;
                    }else{
                        this.item = _.clone(value, true);
                        this.modalShow = !this.modalShow;
                    }
                }
            },
            remove(evt) {
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/projects/" + this.item.id).then((response)=>{
                    if(response.data.success){
                        this.toggleDeleteModal();
                        this.getAll(this.currentPage);
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
        }
    }
</script>

<style >
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