<script>
import SettingsContainer from "@/components/SettingsContainer.vue";

export default {
  components: {
    SettingsContainer,
  },
  props: {
    label: {
      type: String,
    },
  },
};
</script>

<template>
  <SettingsContainer :label="label">
    <div class="w-28 sm:w-full flex flex-col items-center">
      <slot></slot>
    </div>
  </SettingsContainer>
</template>


