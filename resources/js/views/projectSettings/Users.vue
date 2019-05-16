<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <div>
                    <b-navbar type="light" variant="">
                       <!-- <b-nav-form>
                            <b-form-input class="mr-sm-2" type="text" placeholder="Найти" ></b-form-input>
                            <b-button variant="outline-success" class="my-2 my-sm-0" type="submit">Поиск</b-button>
                        </b-nav-form>-->
                        <b-navbar-nav class="ml-auto">
                            <b-button variant="success" @click="newUser()">
                                Новый пользователь
                            </b-button>
                        </b-navbar-nav>
                    </b-navbar>
                </div>
                <b-card :header="caption">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields" >
                        <template slot="parent" slot-scope="data">
                            <span v-if="data.item.parent && data.item.parent.role.name !='admin'">{{ data.item.parent | fullName }}</span>
                        </template>
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                            <i :class="{'icon-trash icons':true, 'text-danger':!data.item.removable}" @click="toggleDeleteModal(data.item)"></i>
                        </template>
                        <template slot="role" slot-scope="data">
                            <span v-text="data.item.role.name"></span>
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" @input="getAll()" hide-goto-end-buttons/>
                    </nav>
                </b-card>
            </b-col>
        </b-row>
        <b-modal id="modalDeleteUser"
                 ref="modal"
                 title="Предупреждение"
                 @ok="remove"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            Вы действительно хотите удалить данного пользователя?
        </b-modal>
        <warning-component ref="warningModal"></warning-component>
    </div>
</template>


<script>
    import { users } from  '../../testData/data';
    import WarningComponent from '../modals/WarningComponent';
    /**
     * Randomize array element order in-place.
     * Using Durstenfeld shuffle algorithm.
     */
    const shuffleArray = (array) => {
        for (let i = array.length - 1; i > 0; i--) {
            let j = Math.floor(Math.random() * (i + 1));
            let temp = array[i];
            array[i] = array[j];
            array[j] = temp
        }
        return array
    };

    export default {
        name: "Users",
        mounted(){
            this.getAll();
        },
        components:{
            WarningComponent
        },
        filters: {
          fullName(value) {
              let name = "";
              name += value.last_name + " " +value.first_name;
              if(value.father_name){
                  name +=value.father_name;
              }
              return name;
          }
        },
        data: () => {
            return {
                caption: "<i class='fa fa-align-justify'></i> Пользователи",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                item: {},
                items: [],
                modalShow:false,
                fields: [
                    {key: 'id',          label:'ID',                sortable: true, thStyle: { width:"100px" }},
                    {key: 'username',    label:'Логин',             sortable: true},
                    {key: 'last_name',   label:'Фамилия',           sortable: true},
                    {key: 'first_name',  label:'Имя',               sortable: true},
                    {key: 'father_name', label:'Отчество',          sortable: true},
                    {key: 'email',       label:'Email',             sortable: true},
                    {key: 'phone',       label:'Телефон',           sortable: true},
                    {key: 'parent',       label:'Клиент (родитель)',           sortable: true},
                    //{key: 'registered',  label:'Зарегистрирован',   sortable: true},
                    {key: 'role',        label:'Роль',              sortable: true},
                    {
                        key: 'actions',
                        label: '',
                        sortable: false,
                        class:"action"
                    }
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0
            }
        },
        methods: {
            getBadge (status) {
                return status === 'Active' ? 'success'
                    : status === 'Inactive' ? 'secondary'
                        : status === 'Pending' ? 'warning'
                            : status === 'Banned' ? 'danger' : 'primary'
            },
            getRowCount (items) {
                return items.length
            },
            clearItem () {
                this.item = {}
            },
            handleSave (evt) {
                evt.preventDefault();
                if(!this.item.id){
                    this.item.id = this.items.length;
                    this.item.registered = Date();
                    this.items.push(this.item);
                }
                this.$root.$emit('bv::hide::modal','modalPrevent');
            },
            edit (value) {
                this.$router.push({ path: 'users/form/' + value.id })
            },
            newUser () {
                this.$router.push({ path: 'users/form' })
            },
            getAll(){
                axios.get(configs.apiUrl + "/users?page=" + (this.currentPage - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Users;
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
                        this.$refs.warningModal.toggle('Удаление данного пользователя недоступно.');
                    }else{
                        this.item = _.clone(value, true);
                        this.modalShow = !this.modalShow;
                    }
                }
            },
            remove(evt) {
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/users/" + this.item.id).then((response)=>{
                    if(response.data.success){
                        this.toggleDeleteModal();
                        this.getAll();
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
        }
    }
</script>

<style>
    .icons{
        margin-left: 10px;
        cursor: pointer;
    }
    .action {
        width: 70px !important;
    }
</style>