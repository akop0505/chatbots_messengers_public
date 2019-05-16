<template>
    <div class="animated fadeIn" @click="clickEverywhere">
        <b-row >
            <b-col xs="12" md="12">
                <dialog-filters></dialog-filters>
            </b-col>
            <b-col xs="4" md="4" class="full-height">
                <b-card header-tag="header"
                        footer-tag="footer" class="full-height b-card-users">
                    <b-list-group class="list-group-accent">
                        <b-list-group-item class="list-group-item-accent-secondary bg-light text-center font-weight-bold text-muted text-uppercase small">
                            Пользователи
                        </b-list-group-item>
                        <b-list-group-item href="javascript:void(0)" :class="{'list-group-item-accent-warning list-group-item-divider':true,'active-user':isSelectedUser(user)}" v-for="(user,index) in  users" :key="'user'+index" @click="openDialog(user,index)">
                            <user-component :user="user"></user-component>
                        </b-list-group-item>
                    </b-list-group>
                </b-card>
            </b-col>
            <b-col xs="8" md="8" class="dialog-right-block">
                <b-card header-tag="header"
                        footer-tag="footer"
                        body-class="card-body-dialog"
                        class="full-height">
                    <dialog-head :selectedChannel="selectedUser" v-if="selectedUser" slot="header"></dialog-head>
                    <div class="history">
                        <div class="full-height">
                            <b-list-group :class="{'list-group-accent':true,'dialog-block':selectedUser,'dialog-block-empty':!selectedUser} " ref="dialogBlock">
                                <template v-for="(dialogMessage,dmKey) in dialogMessages">
                                    <dialog-message :dialogMessage="dialogMessage"></dialog-message>
                                </template>
                                <h6 class="text-center text-muted pre-show" v-if="dialogMessages.length <= 0 && dialogHistoryLoading == false ">Здесь будет выводиться история переписки.</h6>
                                <div class="history-loader" v-if="dialogHistoryLoading">
                                    <circle-spin :loading="dialogHistoryLoading"></circle-spin>
                                </div>
                            </b-list-group>
                        </div>
                        <div class="input-parrent" v-if="selectedUser">
                            <b-form-group label-for="textareaMessage">
                                <b-input-group class="input-group-message-block">
                                    <div class="border border-2 rounded send-message-block">
                                        <div id="uploaded_files_block"  v-if="file !== null">
                                            <i class="fas fa-file fa-2x"></i>
                                            <span>{{file}}</span>
                                        </div>
                                        <div v-if="isLoading">
                                            <circle-spin :loading="isLoading" class="my-circle-spin" ></circle-spin>
                                        </div>
                                        <textarea id="textareaMessage" class="basicTextarea"  rows="2" v-model="messageText" @keyup="handleKeyUp" placeholder="Введите сообшение..."  ></textarea>
                                    </div>
                                    <b-input-group-append>
                                        <div class="parrent-smiles">
                                            <b-btn id="popoverButton-open"><i class="icon-emotsmile"></i></b-btn>

                                            <b-popover placement="top" target="popoverButton-open" :show.sync="popEmoji">
                                                <template>
                                                    <picker set="emojione" @select="addEmoji" class="emoji" :sheetSize="32"/>
                                                </template>
                                            </b-popover>
                                            <div v-if="file != null" class="file_input_label" @click="removeFile">
                                                <i class="far fa-times-circle"></i>
                                            </div>
                                            <label v-if="file == null" class="file_input_label" for="select_file_id" >
                                                <i class="icon-paper-clip"></i>
                                            </label>
                                            <input type="file" id="select_file_id" name="file" @change="uploadFile" :value="fileInputValue" class="file_input">
                                        </div>
                                        <b-button variant="success" @click="send">Отправить</b-button>
                                    </b-input-group-append>
                                </b-input-group>
                            </b-form-group>
                        </div>
                    </div>

                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import { Picker } from 'emoji-mart-vue';
    import UserComponent from './Components/UserComponent';
    import DialogHead from './Components/DialogHead';
    import DialogMessage from './Components/DialogMessage';
    import DialogFilters from './Components/DialogFilters';
    import ChannelThumbnail from './../../components/ChannelThumbnail';

    
    export default {
        name: "Dialog",
        components: {
            Picker,
            UserComponent,
            DialogMessage,
            ChannelThumbnail,
            DialogHead,
            DialogFilters
        },
        sockets: {
            connect: function () {
                console.log('socket connected')
            },
            customEmit: function (data) {
                console.log('this method was fired by the socket server. eg: io.emit("customEmit", data)')
            }
        },
        data(){
            return {
                users: [],
                selectedUser:null,
                dialogMessages: [],
                messageText: "",
                file: null,
                popEmoji:false,
                filters:{},
                isLoading:false,
                dialogHistoryLoading:false,
                count:0
            }
        },
        mounted(){
            this.$on("onUpdateFilter",function(filter){
                this.getUsers(filter);
            });
            this.getUsers();
        },
        updated(){
            let objDiv = document.getElementsByClassName("dialog-block")[0];
            if(objDiv){
                objDiv.scrollTop = objDiv.scrollHeight;
                setTimeout(()=>{objDiv.scrollTop = objDiv.scrollHeight;},700);
            }
        },
        computed:{
            fileInputValue(){
                if(this.file === null){
                    return "";
                }
            }
        },
        methods: {
            handleKeyUp(event){
                this.heightResize();
                if(event.keyCode !== undefined && event.keyCode === 13){
                    if(event.shiftKey){
                        return;
                    }
                    this.send(event);
                }
            },
            heightResize(){
                let count = this.messageText.split(/\r\n|\r|\n/).length;
                let element = document.getElementById('textareaMessage');
                if(count >= 2 && count <= 12){
                    element.rows = count;
                }
                if(count < 2){
                    element.rows = 2;
                }
            },
            removeFile(){
                this.isLoading = false;
                this.file = null;
            },
            isSelectedUser(user){
                if(!this.selectedUser){
                    return false;
                }
                if(user.id == this.selectedUser.id){
                    return true;
                }
                return false;
            },
            addEmoji(event) {
                this.messageText += event.native;
            },
            clickEverywhere(event){
                this.popEmoji = false;
            },
            send(){
                if(this.file != null){
                    this.sendFile();
                }
                this.messageText = this.messageText.replace(/^(\r\n\t|\n|\r\t)$/gm, "");
                if(this.messageText.length <= 0){
                    return;
                }
                const data =  { ...this.selectedUser, message: this.messageText };
                this.messageText = "";
                this.count = 0;
                document.getElementById('textareaMessage').rows = 2;
                axios.post(configs.apiUrl + "/dialog/send",data).then((response)=>{
                    if(response.data.success === true) {
                        this.dialogMessages.push(response.data.DialogMessage);
                    }
                }).catch((e)=>{
                    console.error(e);
                });
            },
            getUsers(filter = {}){
                axios.get(configs.apiUrl + "/dialog/users",{
                    params:filter
                }).then((response)=>{
                    if(response.data.success === true) {
                        this.users = response.data.Users;
                    }
                }).catch((e)=>{
                    console.error(e);
                });
            },
            openDialog(user,index){
                if(this.selectedUser && this.selectedUser.id !== user.id){
                    this.messageText = "";
                    this.file = null;
                }
                this.selectedUser = {...user.user.from, ...user};
                const { chat_id, channel } = user;
                let  bot_username;
                switch (user.channel){
                    case "telegram":
                        bot_username = user.bot.username;
                        break;
                    case "viber":
                    case "fb":
                        bot_username = user.bot_id;
                        break;
                }
                this.dialogHistoryLoading = true;
                this.dialogMessages = [];
                axios.get(configs.apiUrl + "/dialog/messages",{ params:{ chat_id, channel, bot_username }}).then((response)=>{
                    if(response.data.success === true) {
                        this.dialogMessages = response.data.DialogMessages;
                        this.$set(this.users[index],'not_seen',0);
                        this.dialogHistoryLoading = false;
                    }
                }).catch((e)=>{
                    console.error(e);
                });
            },
            uploadFile(event){
                this.isLoading = true;
                const file = event.target.files[0];
                let formData = new FormData();
                formData.append('file',file);
                axios.post(configs.apiUrl + "/dialog/uploadFile",formData,{"headers":{"Content-Type":"multipart/form-data"}}).then((response)=>{
                    this.isLoading = false;
                    if(response.data.success === true) {
                        this.file = response.data.file_path;
                    }
                }).catch((e)=>{
                    this.isLoading = false;
                    this.file = null;
                    const {data} = e.response;
                    if(data.errorType == "VALIDATION_ERROR"){
                        for (let errorKey in  data.errors){
                            alert(data.errors[errorKey]);
                        }
                    }
                });
            },
            sendFile(){
                let data = {
                    'file': this.file,
                    'username': this.selectedUser['bot']['username'],
                    //'bot_id': this.selectedUser['bot_id'],
                    'chat_id': this.selectedUser['chat_id'],
                    'channel': this.selectedUser['channel']
                };
                switch (this.selectedUser['channel']){
                    case 'telegram':
                        data.bot_id  = this.selectedUser['bot']['id'];
                        break;
                    case 'viber':
                    case 'fb':
                        data.bot_id  = this.selectedUser['bot_id'];
                        break;
                }
                this.isLoading = true;
                this.file = null;
                axios.post(configs.apiUrl + "/dialog/sendFile",data).then((response)=>{
                    if(response.data.success === true) {
                        this.isLoading = false;
                        this.messageText = "";
                        //this.file = null;
                        this.dialogMessages.push(response.data.DialogMessage);
                    }
                }).catch((e)=>{
                    this.isLoading = false;
                    console.error(e.response);
                });

            }
        },
    }
</script>

<style>
    .my-circle-spin div{
        /*margin: 0px auto !important;*/
        margin: 3px !important;
        width: 20px !important;
        height: 20px !important;
    }
    .breadcrumb{
        margin-bottom: 0.5em;
    }
    .container-fluid{
        padding-left: 0.5em !important;
        padding-right: 0.5em !important;
    }
</style>
<style scoped>
    .card{
        margin-bottom: 0.5rem;
    }
    #popoverButton-open{
        border-radius: unset;
    }
    .parrent-smiles{
        display: flex;
        align-items: stretch;
    }
    /*.popover{
        max-width: none;
    }*/
    .full-height, .container-fluid {
        height: unset;
    }
    .input-parrent{
        padding-left: 1px;
        padding-right: 1px;
        /*position:absolute;*/
        /*bottom:1px;*/
        width: 100%;
        background-color: white;
        z-index: 1000;
    }
    .input-parrent .form-group{
        margin-bottom: 0rem;
    }
    .input-parrent .basicTextarea{
        width: 100%;
        height: auto;
        max-height: 100px;
        resize: none;
        border: none;
    }
    .basicTextarea:focus{
        outline: none;
    }
    .dialog-block{
        height: 37rem;
        overflow-y:auto
        /*padding-bottom: 1rem;*/
    }
    .b-card-users {
        position: relative;
        overflow-y: auto;
        /*height: 99%;*/
        height: 44.8rem;
    }
    .dialog-block-empty {
        height: 44.6rem;
        overflow-y:auto
    }
    .card-body-dialog{
        padding: 0;
    }
    .emoji{
        max-width: 252px;
    }
    .list-group-item-divider::before{
        left: 0 !important;
        width: 100% !important;
    }
    .active-user{
        background-color: #e4e7ea;
    }
    .file_input_label{
        width: 39px;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #c8ced3;
        cursor: pointer;
        transition: all 300ms linear;
    }
    .file_input_label:hover{
        background-color: #a5aeb7;
    }
    .file_input
    {
        width: 0px;
        height: 0px;
        position: absolute;
        filter: alpha(opacity=0);
        opacity: 0;
    }
    #uploaded_files_block{
        margin: 5px;
        text-align: left;
        padding-right: 0px;
    }
    .send-message-block{
        width: inherit;
        border: 2px solid #c8ced3 !important;
    }
    .dialog-right-block{
        padding-left: 0;
    }
    .dialog-right-block .history{
        display:flex;
        flex-direction: column;
    }
    .pre-show{
        margin: 20.3rem auto;
    }
    .history-loader{
        margin: 10rem auto;
    }
    .input-group-message-block{
        flex-wrap:nowrap;
        z-index:1000;
    }
</style>