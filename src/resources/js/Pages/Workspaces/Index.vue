<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '../../Components/AppLayout.vue'
import PageHeader from '../../Components/PageHeader.vue'
</script>

<template>
  <Head title="ワークスペース" />

  <AppLayout active="projects">
    <section class="placeholder-panel">
      <PageHeader title="ワークスペース" description="ワークスペース情報を確認できます。" />
    </section>
  </AppLayout>
</template>
