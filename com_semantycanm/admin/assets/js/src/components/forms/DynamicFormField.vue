// DynamicFormField.vue
<template>
  <template v-if="field.type === 503">
    <div v-for="(color, i) in field.defaultValue" :key="i">
      <n-color-picker
          :value="color"
          :show-alpha="false"
          :actions="['confirm', 'clear']"
          @update:value="newValue => handleColorChange(i, newValue)"
          style="margin-right: 5px; width: 80px"
      />
    </div>
  </template>
  <template v-else-if="field.type === 501">
    <n-input-number
        v-model:value="field.defaultValue"
        @update:value="newValue => handleFieldChange(newValue)"
        style="width: 150px;"
    />
  </template>
  <template v-else>
    <n-input
        v-model:value="field.defaultValue"
        @update:value="newValue => handleFieldChange(newValue)"
        style="width: 100%;"
    />
  </template>
</template>

<script>
import {defineComponent} from 'vue'
import {NColorPicker, NInputNumber, NInput} from 'naive-ui'

export default defineComponent({
  name: 'DynamicFormField',
  components: {NColorPicker, NInputNumber, NInput},
  props: {
    field: {
      type: Object,
      required: true
    }
  },
  emits: ['update:field'],
  setup(props, {emit}) {
    const handleColorChange = (index, newValue) => {
      const updatedColors = [...props.field.defaultValue];
      updatedColors[index] = newValue;
      emit('update:field', {...props.field, defaultValue: updatedColors});
    };

    const handleFieldChange = (newValue) => {
      emit('update:field', {...props.field, defaultValue: newValue});
    };

    return {
      handleColorChange,
      handleFieldChange
    };
  }
})
</script>