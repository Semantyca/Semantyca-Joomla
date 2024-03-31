<template>
  <div class="html-content" v-html="html"></div>
</template>

<script>
export default {
  props: ['html']
}
</script>

<style scoped>
.html-content {
  max-height: 500px; /* Adjust the height as needed */
  overflow-y: auto; /* Enable vertical scrolling */
  width: 100%; /* Ensure it takes the full width of its container */
}
</style>
