<script setup lang="ts">
import { ref, computed, reactive } from "vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps<{
    property: String;
    message?: String;
  }>();

  const visible = ref(false);
    const content = ref(null);
    const timer = ref(null);
    content.value = computed(() => {
    let msg =  props.property
      ? usePage().props.flash.message &&
          usePage().props.flash.message[props.property]
      : usePage().props.flash.message;
    if (props.message) msg = props.message;
    if ( msg) { 
      unhide();
      timer.value = setTimeout(hide, 5000);
    }
    return msg;
  });
    function unhide() {
      visible.value = true;
    }
    function hide() {
      visible.value = false;
    }
    function stopTimeOut() {
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
      top-4
      left-1/2
      transform
      -translate-x-1/2
      p-4
      w-full
      max-w-max max-h-screen
      overflow-y-auto
      bg-red-700
      hover:cursor-pointer
      text-white
      rounded-lg
    "
    @click="hide()"
    @mouseenter="stopTimeOut()"
    @mouseleave="setFinalTimeOut()"
    v-html="
      '<div class=\'text-center sm:text-left font-semibold\'>Ωχ λάθος!</div><div>' +
      content.value +
      '</div>'
    "
  ></div>
</template>

