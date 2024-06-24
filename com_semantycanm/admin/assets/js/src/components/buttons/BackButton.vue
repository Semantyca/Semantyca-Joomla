<template>
  <n-button size="large" type="info" @click="$emit('back')">
    <template #icon>
      <n-icon>
        <arrow-bar-left />
      </n-icon>
    </template>
    {{ text }}
  </n-button>
</template>

<script>
import {NButton, NIcon} from 'naive-ui'
import {ArrowBarLeft} from '@vicons/tabler'

export default {
  name: 'BackButton',
  components: {
    NButton,
    NIcon,
    ArrowBarLeft
  },
  props: {
    text: {
      type: String,
      default: 'Back'
    }
  },
  emits: ['back']
}
</script>
