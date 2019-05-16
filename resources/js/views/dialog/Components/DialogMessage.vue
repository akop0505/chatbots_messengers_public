<template>
    <b-list-group-item :class="{'middle': true ,'sender': position === 'sender', 'receiver': position === 'receiver' }">
        <div :class="{avatar: true,'float-right': position === 'sender', 'float-left': position === 'receiver'}">
            <img class="img-avatar hidden" src="../../../img/avatars/default_avatar.jpg" alt="admin@bootstrapmaster.com" >
            <img class="img-avatar" :src="'/images/default_avatar.jpg'" alt="admin@bootstrapmaster.com">
        </div>

        <b-card v-if="type == 'text'" class="message">
            <div  v-html="message"></div>
        </b-card>
        <b-card v-if="type == 'file'" class="message">
            <video v-if="mimeTypeIs == 'video'" width="320" height="240" controls :type="dialogMessage.file_json.mime_type">
                <source :src="dialogMessage.file_path" >
                Your browser does not support the video tag.
            </video>
            <audio v-if="mimeTypeIs == 'audio'" controls>
                <source :src="dialogMessage.file_path" :type="dialogMessage.file_json.mime_type">
                Your browser does not support the audio element.
            </audio>
            <img v-if="mimeTypeIs == 'image'" :src="dialogMessage.file_path" width="100%"/>

            <a v-if="mimeTypeIs == 'unknown'" :href="dialogMessage.file_path" download>
                <i class="fas fa-file fa-9x"></i>
            </a>
        </b-card>
    </b-list-group-item>
</template>

<script>
    export default {
        name: "DialogMessage",
        props:{
            dialogMessage:{
                type: Object,
                required:true
            },
        },
        created(){
            this.load();
        },
        mounted(){
        },
        computed:{
            mimeTypeIs(){
                try {
                    if((this.dialogMessage.file_json.mime_type).indexOf('audio') !== -1){
                        return 'audio'
                    }
                    if((this.dialogMessage.file_json.mime_type).indexOf('video') !== -1 ){
                        return 'video'
                    }
                    if((this.dialogMessage.file_json.mime_type).indexOf('voice') !== -1 ){
                        return 'voice'
                    }
                    if((this.dialogMessage.file_json.mime_type).indexOf('image') !== -1){
                        return 'image'
                    }
                }catch (e) {
                    return "unknown";
                }
            }
        },
        methods:{
            replaceToBr: function(value){
                return value? value.replace(/\n/g, "<br/>"): "";
            },
            load: function(){
                /*receiver*/
                this.type = this.dialogMessage.type ? this.dialogMessage.type : 'text';
                if(["waiting","answered","bot_simple"].indexOf(this.dialogMessage.state) !== -1){
                    this.position = 'sender';
                    if(this.dialogMessage.state == "bot_simple"){
                        this.message = this.replaceToBr(this.dialogMessage.answer);
                    }else{
                        this.message = this.replaceToBr(this.dialogMessage.message.text);
                    }
                }
                if(["user_simple","user_answer"].indexOf(this.dialogMessage.state) !== -1){
                    this.position = 'receiver';
                    this.message = this.replaceToBr(this.dialogMessage.answer);
                }
            },
        },
        data(){
            return {
                position:null,
                message:""
            }
        }
    }
</script>

<style>
    .dialog-block .avatar{
        padding: 5px;
    }
</style>
<style scoped>
    .sender, .receiver{
        max-width: 70%;
        border: none;
    }
    .sender{
        margin-left: auto;
        padding-right: 0 !important;
    }
    .receiver{
        margin-right: auto;
        padding-left: 0 !important;
    }
    .message{
        border-radius: 15px;
        background-color: #f0f3f5;
        margin-bottom: 0;
    }
    .hidden{
        display: none;
    }
</style>