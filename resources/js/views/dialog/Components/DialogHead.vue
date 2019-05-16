<template>
    <div>
        <div>
            <strong v-text="model.name"></strong>
        </div>
        <div class="text-muted mr-3">
            <channel-thumbnail :channel="model.channel" size="fa-1x"></channel-thumbnail>
            {{ model.bot }}
        </div>
    </div>
</template>

<script>
    import ChannelThumbnail from '../../../components/ChannelThumbnail';

    export default {
        name: "DialogHead",
        components:{
            ChannelThumbnail
        },
        props:{
            selectedChannel:{
                type: Object,
                default: {}
            }
        },
        data(){
            return{
                model:{
                    channel:"",
                    name:"",
                    bot:""
                }
            }
        },
        mounted(){
            this.setModel();
        },
        watch:{
            selectedChannel:{
                handler:function(n,o){
                    this.setModel();
                },
                deep:true
            }
        },
        methods:{
            setModel(){
                let user = this.selectedChannel;
                switch (user.channel) {
                    case "telegram":
                        this.$set(this.model,'channel', user.channel);
                        this.$set(this.model,'name',  user.user.from ? user.user.from.first_name + ' ' + user.user.from.last_name : "");
                        this.$set(this.model,'bot', user.bot.first_name);
                        break;
                    case "viber":
                        this.$set(this.model,'channel', user.channel);
                        this.$set(this.model,'name', user.user.name ? user.user.name : "");
                        this.$set(this.model,'bot', user.bot ? user.bot.name: "");
                        break;
                    case "fb":
                        this.$set(this.model, 'channel', user.channel);
                        this.$set(this.model, 'name', user.user.first_name && user.user.last_name ? user.user.first_name +" "+ user.user.last_name : "");
                        this.$set(this.model, 'bot', user.bot ? user.bot.name : "");
                        break;
                }
            }
        }
    }
</script>

<style scoped>

</style>