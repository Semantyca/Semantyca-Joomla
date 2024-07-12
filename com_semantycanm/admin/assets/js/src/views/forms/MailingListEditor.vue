<template>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-page-header :subtitle="formValue.groupName" class="mb-3">
        <template #title>
          Mailing list
        </template>
      </n-page-header>
    </n-gi>
    <n-gi>
      <n-space>
        <n-button type="info" @click="$router.push('/list')">
          <template #icon>
            <n-icon>
              <arrow-big-left/>
            </n-icon>
          </template>
          Back
        </n-button>
        <n-button type="primary" @click="handleSave">
          {{ globalStore.translations.SAVE }}
        </n-button>
        <div v-if="isLoading" class="mt-1">
          <progress-svg :size="24"  />
        </div>
      </n-space>
    </n-gi>
    <n-gi class="mt-4">
      <n-form v-if="!isLoading" ref="formRef" label-placement="left" label-width="auto" :model="formValue" :rules="rules">
        <n-form-item label="User groups" path="selectedGroups">
          <n-select
              v-model:value="formValue.selectedGroups"
              multiple
              filterable
              placeholder="Search user groups"
              :options="mailingListStore.getUserGroupsOptions"
              :clear-filter-after-select="true"
              style="width: 100%; max-width: 600px;"
          />
        </n-form-item>
        <n-form-item label="Group name" path="groupName">
          <n-input v-model:value="formValue.groupName"
                   type="text"
                   id="groupName"
                   style="width: 100%; max-width: 600px;"
                   placeholder="Mailing list group name"/>
        </n-form-item>
      </n-form>
    </n-gi>
  </n-grid>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useGlobalStore } from "../../stores/globalStore";
import {
  NButton, NForm, NFormItem, NGi, NGrid, NIcon, NInput, NSpace, NSelect, NPageHeader
} from "naive-ui";
import { ArrowBigLeft } from '@vicons/tabler'
import { useMailingListStore } from "../../stores/mailinglist/mailinglistStore";
import ProgressSvg from "../../components/ui/ProgressSvg.vue";

const route = useRoute();
const router = useRouter();
const formRef = ref(null);
const formValue = reactive({
  id: route.params.id,
  groupName: '',
  selectedGroups: []
});
const globalStore = useGlobalStore();
const mailingListStore = useMailingListStore();
const isLoading = ref(true);

const fetchInitialData = async () => {
  try {
    const [details, _] = await Promise.all([
      formValue.id ? mailingListStore.getDetails(formValue.id, true) : Promise.resolve(null),
      mailingListStore.fetchUserGroupsList()
    ]);

    if (details) {
      formValue.groupName = details.name;
      formValue.selectedGroups = details.groups.map(group => group.id);
    }
  } catch (error) {
    console.error("Error fetching initial data:", error);
  } finally {
    isLoading.value = false;
  }
};

fetchInitialData();

const handleSave = () => {
  formRef.value.validate(async (errors) => {
    if (!errors) {
      const success = await mailingListStore.saveList(formValue);
      if (success) {
        router.push('/list');
      }
    }
  });
}

const rules = {
  groupName: [
    { required: true, message: 'Please input the group name', trigger: 'blur' }
  ],
  selectedGroups: [
    { type: 'array', min: 1, message: 'Please select at least one user group', trigger: 'change' }
  ]
};
</script>