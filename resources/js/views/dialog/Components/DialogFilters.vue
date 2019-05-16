<template>
    <b-card>
        <b-row>
            <b-col xs="3" md="3">
                <b-form-group>
                    <b-form-select class="mb-3" id="phoneSelect" v-model="filter.channel">
                        <option :value="null">️Все номера</option>
                        <option v-for="(chVal,chKey) in channels" :value="chVal.alias" v-text="chVal.name" :key="'chVal'+chKey"></option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col xs="3" md="3">
                <b-form-group>
                    <b-form-select class="mb-3" id="phoneSelect" v-model="filter.project">
                        <option :value="null">Все проекты</option>
                        <option v-for="(prVal,prKey) in projects" :value="prVal.id" v-text="prVal.name" :key="'prVal'+prKey"></option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <!--<b-col xs="2" md="2" style="display:flex; padding-bottom: 16px;">
                <label style="align-self: center;">Открытие чаты</label>
                <c-switch class="mx-1"
                          label
                          color="success"
                          :checked="false"
                          variant="3d"/>
            </b-col>
            <b-col xs="2" md="2">
                <b-form-group label="Теги:"
                              label-for="basicName"
                              :horizontal="true">
                    <b-form-input id="basicName" type="text" autocomplete="name"></b-form-input>
                </b-form-group>
            </b-col>-->
            <b-col xs="3" md="3" class="text-sm-center" style="display:flex; justify-content: center;">
                <label style="align-self: center;">Только непрочитанные</label>
                <c-switch class="mx-1"
                          label
                          color="success"
                          :checked="false"
                          variant="3d"
                          v-model="filter.not_seen"
                />
            </b-col>
            <b-col xs="3" md="3">
                <b-form-group>
                    <b-input-group>
                        <b-form-input type="text" placeholder="" autocomplete="email" v-model="filter.search"></b-form-input>
                        <b-input-group-append>
                            <b-button variant="primary">Найти</b-button>
                        </b-input-group-append>
                    </b-input-group>
                </b-form-group>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    import { Switch as cSwitch } from '@coreui/vue';

    export default {
        name: "DialogFilters",
        components:{
            cSwitch
        },
        data(){
            return {
                channels:[],
                projects:[],
                openChats:false,
                tags:"",
                filter:{
                    channel: null,
                    project: null,
                    search: "",
                    not_seen: false,
                }
            }
        },
        watch:{
            filter:{
                handler:function(nVal,oVal){
                    this.$parent.$emit("onUpdateFilter",nVal);
                },
                deep:true
            }
        },
        mounted(){
            this.loadAll();
        },
        computed:{
            set(){
                console.log(this.filter.channel);
            }
        },
        methods:{
            loadAll(){
                axios.all([
                    axios.get(configs.apiUrl + "/messengers"),
                    axios.get(configs.apiUrl + "/select/projects")
                ])
                    .then(axios.spread((messengersRes, projectRes) => {
                        let { Messengers } = messengersRes.data;
                        let { Projects } = projectRes.data;
                        this.channels = Messengers;
                        this.projects = Projects;
                    }));
            },
        }
    }
</script>

<style scoped>
    .form-group, .mb-3{
        margin-bottom: 0em !important;
    }
</style>