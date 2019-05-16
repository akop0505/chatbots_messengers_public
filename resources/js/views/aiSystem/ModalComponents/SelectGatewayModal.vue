<template>
    <b-modal title="Новый канал"
             @ok="handleOk"
             ok-title="Добавить"
             cancel-title="Отмена"
             v-model="modal"
    >
        <b-form-select  class="mb-3" id="messengersSelectId" v-model="gateway">
            <option :value="null" selected>Выберите шлюз</option>
            <option v-for="val in gateways" :value="val" v-text="val.name"></option>
        </b-form-select>
    </b-modal>
</template>

<script>
    export default {
        name: "SelectGatewayModal",
        data: () => {
            return {
                modal: false,
                gateways:[],
                gateway:null
            }
        },
        mounted(){
            this.getGateways();
        },
        methods: {
            toggle(){
                this.gateway = null;
                this.modal = !this.modal
            },
            handleOk(evt) {
                evt.preventDefault();
                this.$parent.$emit("select-gateway",this.gateway);
                this.toggle();
                /*if(this.selectedMessenger !== null){
                    let pMessenger = {
                        messenger_id:this.selectedMessenger.id,
                        bot_username:"",
                        bot_name:"",
                        phone:"",
                        gateway_id:null,
                        gateway_token:"",
                        bot_token:"",
                        permission_id:null,
                        messenger:this.selectedMessenger,
                        GatewaySettings:[]
                    };
                    this.item.ProjectMessengers.push(pMessenger);
                    this.toggleModal();
                }*/
            },
            getGateways(){
                axios.get(configs.apiUrl + "/gateways").then(response=>{
                    if(response.data.success === true){
                        this.gateways = response.data.Gateways;
                    }
                    else{
                        this.gateways = {};
                    }
                }).catch(e => {
                    console.log(e);
                })
            },
        }
    }
</script>

<style scoped>

</style>