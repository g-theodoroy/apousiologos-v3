<script setup lang="ts">
  import { ref, computed, reactive } from "vue";
  import { usePage } from "@inertiajs/vue3";

  const props = defineProps<{
    property?: String;
    message?: String;
    html?:Boolean;
  }>();

  const visible = ref(false);
  const content = ref(null);
  const timer = reactive({first:null, second:null});
  content.value = computed(() => {
    let msg =  props.property
      ? usePage().props.flash.message &&
          usePage().props.flash.message[props.property]
      : usePage().props.flash.message;
    if (props.message) msg = props.message;
    if ( msg) { 
      unhide();
      timer.first = setTimeout(hide, 5000);
    }
    return msg;
  });
  
  function unhide(): void {
    visible.value = true;
  }
  function hide() : void{
    visible.value = false;
  }
  function stopTimeOut() : void {
    clearTimeout(timer.first);
  }
  function setFinalTimeOut() : void {
    timer.second = setTimeout(hide, 1000);
  }
  
</script>

<template>
  <div
    v-if="content.value && visible"
    class="
      fixed
      top-16
      left-1/2
      transform
      -translate-x-1/2
      p-4
      text-center
      w-full
      max-w-max
      bg-green-600
      hover:cursor-pointer
      text-white
      rounded-lg
      flex flex-col
      sm:flex-row sm:justify-center
    "
    @click="hide()"
    @mouseenter="stopTimeOut()"
    @mouseleave="setFinalTimeOut()"
  >
    <div class="font-semibold pr-2">Ωραία!</div>
    <div v-if="html" class="w-full"><span v-html="content.value" /> <slot></slot></div>
    <div v-if="!html" class="w-full">{{ content.value }} <slot></slot></div>
  </div>
</template>
