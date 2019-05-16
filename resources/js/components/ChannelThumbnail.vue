<template>
    <span class="messenger-input-group-prepend">
        <i v-if="channel == 'viber'"     :class="'fab fa-viber '+ size"></i>
        <i v-if="channel == 'whatsapp'"  :class="'fab fa-whatsapp '+ size"></i>
        <i v-if="channel == 'telegram'"  :class="'fab fa-telegram '+ size"></i>
        <i v-if="channel == 'vK'"        :class="'fab fa-vk '+ size"></i>
        <i v-if="channel == 'sms'"       :class="'far fa-comment '+ size"></i>
        <i v-if="channel == 'email'"     :class="'far fa-envelope '+ size"></i>
        <i v-if="channel == 'fb'"        :class="'fab fa-facebook-messenger '+ size"></i>
        <b class="pl-2">
            <slot></slot>
        </b>
    </span>
</template>

<script>
    export default {
        name: "ChannelThumbnail",
        props:{
            channel:{
                type: String,
                required: true,
            },
            size:{
                type: String,
                default:"fa-2x"
            }
        },
        updated(){
            //console.log(this.channel);
        }
    }
</script>

<style scoped>

</style>