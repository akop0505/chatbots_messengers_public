<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-navbar type="light" >
                    <b-navbar-nav class="ml-auto flex-row-reverse">
                        <b-nav-item>
                            <b-button-group>
                                <b-button variant="primary" @click="selectSequence()" :disabled="selectedRows.length === 0">
                                    Начать последовательность
                                </b-button>
                                <b-button variant="success" @click="newLead()">
                                    Новый лид
                                </b-button>
                            </b-button-group>
                        </b-nav-item>
                    </b-navbar-nav>
                </b-navbar>
                <b-card :header="caption" v-if="items.length>0">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small"
                             :fixed="fixed" responsive="sm" :items="items" :fields="fields"
                    >
                        <template slot="selected" slot-scope="data">
                            <input type="checkbox" id="checkbox" v-model="data.item.selected">
                        </template>
                        <template slot="actions" slot-scope="data">
                            <span>
                                <i v-if="data.item.passage" class="icon-options icons " @click="showStartedSequence(data.item.passage)"></i>
                                <i class="icon-pencil icons " @click="edit(data.item)"></i>
                                <i class="icon-trash icons " @click="toggleDeleteModal(data.item)"></i>
                            </span>
                        </template>
                        <template slot="fio" slot-scope="data">
                            {{data.item.first_name}} {{data.item.last_name}}
                        </template>
                        <template slot="owner" slot-scope="data">
                            {{data.item.owner.last_name}} {{data.item.owner.first_name}}
                        </template>
                        <template slot="project" slot-scope="data">
                            {{data.item.project.name}}
                        </template>
                        <template slot="status" slot-scope="data">
                            {{data.item.status.name}}
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons @input="getAll(currentPage)"/>
                    </nav>
                </b-card>
                <b-card v-else>
                    <b-alert show variant="warning" class="mb-0">Вы не добавили еще ни одного лида, добавьте лид для начала работы.</b-alert>
                </b-card>
            </b-col>
        </b-row>
        <b-modal id="modalDeleteLead"
                 ref="modal"
                 title="Предупреждение"
                 @ok="remove"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            Вы действительно хотите удалить данный лид?
        </b-modal>
        <b-modal id="modalSelectSequence"
                 ref="modal"
                 title="Выбор исполняемой последовательности"
                 @ok="startSequence"
                 ok-title="Начать"
                 cancel-title="Отмена"
                 v-model="modalShowSelectSequence"
        >
            <b-row>
                <b-col sm="12">
                    <b-form-group>
                        <label>Последовательность</label>
                        <model-list-select
                                :class="{'form-control':true}"
                                :list="sequences"
                                v-model="sequence"
                                option-value="id"
                                option-text="name"
                                :selected-option="sequence"
                                placeholder="Выберите последовательность">
                        </model-list-select>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-modal>
        <b-modal id="modalEditPassage"
                 size="lg"
                 ref="modal"
                 title="Редактирование исполняемой последовательности"
                 :ok-only="true"
                 ok-title="Закрыть"
                 @ok="getAll()"
                 v-model="modalEditPassage"
        >
            <b-row>
                <b-col sm="12">
                    <passage-edit
                            v-model="bufferPassage"
                    ></passage-edit>
                </b-col>
            </b-row>
        </b-modal>
    </div>
</template>


<script>
    import { ModelListSelect } from 'vue-search-select';
    import axios from 'axios';
    import { vSwitch, vCase, vDefault } from 'v-switch-case';
    import { leads } from '../../testData/data';
    import PassageEdit from "./components/PassageEdit";

    export default {
        name: "Sequences",
        directives: {
            'switch': vSwitch,
            'case': vCase,
            'default': vDefault
        },
        components: {
            PassageEdit,
            ModelListSelect,
        },
        mounted(){
            this.getAll();
            this.loadSequences();
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
        data: function() {
            return {
                modalShow: false,
                modalShowSelectSequence: false,
                modalEditPassage: false,
                caption: "<i class='fa fa-align-justify'></i> Лиды",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:false,
                item: {},
                items: [],
                fields: [
                    {key: 'selected',       label:  '',             sortable: false, class: 'select'},
                    {key: 'fio',            label:  'ФИО',          sortable: true},
                    {key: 'email',          label:  'Email',        sortable: true},
                    {key: 'phone',          label:  'Телефон',      sortable: true},
                    {key: 'project',        label:  'Проект',       sortable: true},
                    {key: 'owner',          label:  'Менеджер',     sortable: true},
                    {key: 'status',         label:  'Статус',       sortable: true},
                    {key: 'actions',        label:  '',             sortable: false, class:'action'},
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0,
                sequences: [],
                sequence: null,
                bufferPassage: null,
            }
        },
        computed: {
            selectedRows () {
                return this.items.filter(function (item) {
                    return item.selected === true;
                });
            }
        },
        methods: {
            edit (value) {
                this.item = _.clone(value, true);
                this.$router.push({ name: 'leads.form',params: { id: value.id  }});
            },
            newLead(){
                this.$router.push({ name: 'leads.form' });
            },
            getAll(current) {
                if(current === undefined){
                    current = 1;
                }
                axios.get(configs.apiUrl + "/leads?page=" + (current - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Leads;
                        this.totalRows = response.data.totalRows
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
            toggleDeleteModal(value) {
                if(this.modalShow){
                    this.item = {};
                }
                else if(value !== undefined){
                    this.item = _.clone(value, true);
                }
                this.modalShow = !this.modalShow;
            },
            remove(evt) {
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/leads/" + this.item.id).then((response)=>{
                    if(response.data.success){
                        this.toggleDeleteModal();
                        this.getAll(this.currentPage);
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },

            loadSequences() {
                axios.get(configs.apiUrl + "/select/sequences/").then(response=>{
                    if(response.data.success === true){
                        this.sequences = response.data.Sequences;
                    }
                    else{
                        this.sequences = {};
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            showStartedSequence(passage){
                this.bufferPassage = Object.assign({}, passage);
                this.modalEditPassage = true;
            },
            selectSequence(){
                this.modalShowSelectSequence = true;
            },
            startSequence(){
                let leads = this.selectedRows.map(function (row) {
                    return row.id;
                });

                axios.post(configs.apiUrl + "/sequences/start/" + this.sequence, {leads}).then(response=>{
                    if(response.data.success === true){
                        this.getAll();
                    }else{
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        }
    }
</script>

<style scoped>
    .messengers .fab{
        margin:2px;
    }
    .icons{
        margin-left: 10px;
        cursor: pointer;
    }
    .animated >>> .action {
        width: 100px !important;
    }
    .select {
        text-align: center;
        width: 30px !important;
    }
</style>