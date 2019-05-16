<template>
    <div>
        <div class="avatar float-right">
            <img class="img-avatar" :src="'/images/default_avatar.jpg'" alt="admin@bootstrapmaster.com">
        </div>
        <div>
            <channel-thumbnail :channel="model.channel" size="fa-1x"></channel-thumbnail>
            <!--<i class="fab fa-viber" style="color: #7b519d;"></i>-->
            <strong v-text="model.name"></strong>
            <b-badge pill variant="info" class="float-right" v-if="user.not_seen">{{ user.not_seen }}</b-badge>
        </div>
        <small class="text-muted mr-3" v-text="model.bot">
        </small>
        <!--<small class="text-muted mr-3">
            13 ноя в 13:43
        </small>-->
    </div>
</template>

<script>
    import ChannelThumbnail from '../../../components/ChannelThumbnail';

    export default {
        components: {
            ChannelThumbnail
        },
        watch:{
          user:{
              handler: function(){
                  this.parseToJson();
                  this.setModel();
              },
              deep:true
          }
        },
        name: "UserComponent",
        mounted(){
            this.parseToJson();
            this.setModel();
        },
        props:{
            user:{
                type: Object,
                required: true
            }
        },
        data(){
          return {
              model:{
                  channel:"",
                  full:"",
                  short:"",
                  date: ""
              }
          }
        },
        methods: {
            parseToJson(){
                let user = typeof this.user.user == "string" ? JSON.parse(this.user.user): this.user.user;
                let bot = typeof this.user.bot == "string" ? JSON.parse(this.user.bot) : this.user.bot;
                this.$set(this.user,'user',user);
                this.$set(this.user,'bot',bot);
            },
            setModel(){
                let user = this.user;
                switch (user.channel) {
                    case "telegram":
                        this.$set(this.model,'channel',user.channel);
                        this.$set(this.model,'name',user.user.from ? user.user.from.first_name + ' ' + user.user.from.last_name : "");
                        this.$set(this.model,'bot',user.bot.first_name);
                        break;
                    case "viber":
                        this.$set(this.model,'channel',user.channel);
                        this.$set(this.model,'name',user.user.name ? user.user.name : "");
                        this.$set(this.model,'bot',user.bot ? user.bot.name: "");
                        break;
                    case "fb":
                        this.$set(this.model,'channel',user.channel);
                        this.$set(this.model,'name', (user.user.first_name && user.user.last_name) ? user.user.first_name +' '+user.user.last_name : "");
                        this.$set(this.model,'bot',user.bot ? user.bot.name : "");
                        break;
                }
            }
        }
    }
</script>

<style scoped>

</style>