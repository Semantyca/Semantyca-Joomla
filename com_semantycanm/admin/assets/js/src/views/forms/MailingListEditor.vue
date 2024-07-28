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
      </n-space>
    </n-gi>
    <n-gi class="mt-4">
      <n-form ref="formRef" label-placement="left" label-width="auto" :model="formValue"
              :rules="rules">
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
import {ref, reactive} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import {useGlobalStore} from "../../stores/globalStore";
import {
  NButton, NForm, NFormItem, NGi, NGrid, NIcon, NInput, NSpace, NSelect, NPageHeader, useMessage, useLoadingBar
} from "naive-ui";
import {ArrowBigLeft} from '@vicons/tabler'
import {useMailingListStore} from "../../stores/mailinglist/mailinglistStore";
import {handleNotOkError, handleSuccess} from "../../utils/apiRequestHelper";

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
const msgPopup = useMessage();
const loadingBar = useLoadingBar();

const fetchInitialData = async () => {
  loadingBar.start();
  try {
    const [details, _] = await Promise.all([
      formValue.id ? mailingListStore.getDetails(formValue.id, true) : Promise.resolve(null),
      mailingListStore.fetchUserGroupsList()
    ]);

    if (details) {
      console.log('details ', details);
      formValue.groupName = details.data.name;
      formValue.selectedGroups = details.data.groups.map(group => group.id);
    }
  } catch (error) {
    loadingBar.error();
    console.error("Error fetching initial data:", error);
  } finally {
    loadingBar.finish();
  }
};

fetchInitialData();

const handleSave = () => {
  loadingBar.start();
  try {
    formRef.value.validate(async (errors) => {
      if (!errors) {
        const response = await mailingListStore.saveList(formValue);
        console.log(response);
        if (!response.ok) {
          await handleNotOkError(msgPopup, response);
        } else {
          await handleSuccess(msgPopup, response);
          router.push('/list');
        }
      }
    });
  } catch (error) {
    loadingBar.error();
    console.error("Error saving mailing list", error);
  } finally {
    loadingBar.finish();
  }
}

const rules = {
  groupName: [
    {required: true, message: 'Please input the group name', trigger: 'blur'}
  ],
  selectedGroups: [
    {type: 'array', min: 1, message: 'Please select at least one user group', trigger: 'change'}
  ]
};

</script>