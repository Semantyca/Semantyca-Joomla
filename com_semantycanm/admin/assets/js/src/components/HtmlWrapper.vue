<template>
  <!--  <div v-html="html"></div>-->
  <div v-html="'<p>Static test content</p>'"></div>
</template>

<script>
export default {
  props: ['html'],
  mounted() {
    console.log(this.html);
  }
}
</script>
