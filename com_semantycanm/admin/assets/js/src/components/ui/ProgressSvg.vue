<template>
  <svg :width="size" :height="size" viewBox="0 0 24 24">
    <circle
        cx="12"
        cy="12"
        r="10"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-dasharray="56.54866776461628"
        stroke-dashoffset="0"
    >
      <animateTransform
          attributeName="transform"
          attributeType="XML"
          type="rotate"
          from="0 12 12"
          to="360 12 12"
          dur="1s"
          repeatCount="indefinite"
      />
    </circle>
  </svg>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  size: {
    type: Number,
    default: 24,
  },
});
</script>