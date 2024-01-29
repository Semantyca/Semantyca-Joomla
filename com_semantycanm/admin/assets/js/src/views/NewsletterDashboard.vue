<template>
  <div class="container">
    <div class="row">
      <div class="col-md-6 ">
        <div class="header-container">
          <h3>{{ store.translations.AVAILABLE_LISTS }}</h3>
        </div>
        <div class="col-md-12 dragdrop-list">
          <ul ref="availableListsUlRef" class="list-group">
            <li v-for="ml in store.mailingList.docs" :key="ml.id" class="list-group-item"
                :id="ml.id">{{ ml.name }}
            </li>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <h3>{{ store.translations.SELECTED_LISTS }}</h3>
        <div class="col-md-12 dragdrop-list">
          <ul ref="selectedListsUlRef" class="dropzone list-group"></ul>
        </div>
      </div>
    </div>

    <div class="row justify-content-center mt-5 submitarea">
      <div class="col-md-12">
        <h2 class="mb-4">{{ store.translations.SEND_NEWSLETTER }}</h2>
        <input type="hidden" id="currentNewsletterId" name="currentNewsletterId" value="">
        <input type="hidden" id="hiddenSelectedLists" name="selectedLists" value="">

        <label for="testEmails">{{ store.translations.TEST_ADDRESS }}</label>
        <n-input v-model="state.testEmails"
                 placeholder="E-mail address"
                 type="text"
                 id="testEmails"
                 name="testEmails"/>

        <div class="input-group mb-3 mt-3">
          <n-input v-model="state.subject"
                   type="text"
                   id="subject"
                   placeholder="Subject"
                   aria-label="Subject"/>
          <!--          <n-button class="btn btn-outline-secondary"
                              id="addSubjectBtn"
                              @click="setSubject"
                              style="margin: 5px;">{{ store.translations.FETCH_SUBJECT }}
                    </n-button>-->
        </div>

        <div class="form-group">
          <label for="messageContent">{{ store.translations.MESSAGE_CONTENT }}</label>
          <n-input
              v-model:value="ownStore.messageContent"
              type="textarea"
              rows="10"
              placeholder=""
          />
        </div>
        <n-space>
          <n-button type="primary"
                    @click="sendNewsletter">{{ store.translations.SEND_NEWSLETTER }}
          </n-button>
          <n-switch :round="false" :rail-style="railStyle">
            <template #checked>
              On
            </template>
            <template #unchecked>
              Off
            </template>
          </n-switch>
          <n-button id="saveNewsletterBtn"
                    type="primary"
                    @click="saveNewsletter">{{ store.translations.SAVE_NEWSLETTER }}
          </n-button>
          <n-button id="toggleEditBtn"
                    type="primary"
                    @click="editContent">{{ store.translations.EDIT }}
          </n-button>
        </n-space>
      </div>
    </div>

    <div style="margin-top: 20px">
      <h3 class="mb-4">{{ store.translations.NEWSLETTERS_LIST }}</h3>
    </div>
    <div>
      <n-data-table
          :columns="columns"
          :data="ownStore.newsLetterDocsView.docs"
          :bordered="false"
          :pagination="pagination"
      />
    </div>
  </div>

</template>

<script>
import {defineComponent, nextTick, onMounted, reactive, ref} from 'vue';
import {useGlobalStore} from "../stores/globalStore";
import {NButton, NDataTable, NInput, NSpace, NSwitch} from "naive-ui";
import {useNewsletterStore} from "../stores/newsletterStore";

export default defineComponent({
  name: 'NewsletterComponent',
  components: {
    NInput,
    NButton,
    NSpace,
    NSwitch,
    NDataTable
  },

  setup() {
    const availableListsUlRef = ref(null);
    const selectedListsUlRef = ref(null);
    const store = useGlobalStore();
    const ownStore = useNewsletterStore();
    const newsletterRequest = new NewsletterRequest();
    const state = reactive({
      currentNewsletterId: '',
      testEmails: '',
      subject: '',
      isTest: false
    });

    const validateNewsletterInputs = () => {
      if (ownStore.messageContent === '') {
        showWarnBar("Message content is empty. It cannot be saved");
        return false;
      }
      if (state.subject === '') {
        showWarnBar("Subject cannot be empty");
        return false;
      }
      return true;
    };
    const validateEmailFormat = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    };
    const setSubject = () => {
      fetch('index.php?option=com_semantycanm&task=service.getSubject&type=random')
          .then(response => response.json())
          .then(data => {
            state.subject = data.data;
          })
          .catch(error => {
            console.log(error);
            showAlertBar("Error: " + error);
          });
    }
    const sendNewsletter = async () => {
      if (!validateNewsletterInputs()) return;
      const testEmails = state.testEmails.trim();

      if (testEmails && !validateEmailFormat(testEmails)) {
        showWarnBar('Invalid email format in test emails');
        return;
      }
      let listItems;
      if (testEmails !== "") {
        listItems = [testEmails];
      } else {
        listItems = Array.from(document.querySelectorAll('#selectedLists li'))
            .map(li => li.textContent.trim());
        if (listItems.length === 0) {
          showWarnBar('The list is empty');
          return;
        }
      }

      try {
        await newsletterRequest.sendEmail(state.subject, state.messageContent, listItems);
      } catch (error) {
        showAlertBar("Error: " + error);
      }
    };

    const saveNewsletter = () => {
      if (!validateNewsletterInputs()) return;
      const newsletterRequest = new NewsletterRequest();
      newsletterRequest.addNewsletter(state.subject, state.messageContent)
          .then(() => {
            refreshNewsletters(1);
          })
    };

    const editContent = () => {
      const messageContent = document.getElementById('messageContent');
      const toggleBtn = document.getElementById('toggleEditBtn');
      if (messageContent.hasAttribute('readonly')) {
        messageContent.removeAttribute('readonly');
        toggleBtn.textContent = 'Read-Only';
      } else {
        messageContent.setAttribute('readonly', 'readonly');
        toggleBtn.textContent = 'Edit';
      }
    };

    const applyAndDropSet = (lists) => {
      lists.forEach(list => {
        Sortable.create(list, {
          group: {
            name: 'shared',
            pull: true,
            put: true
          },
          animation: 150,
          sort: false
        });
      });
    };

    onMounted(() => {
      store.getPageOfMailingList(1);
      ownStore.fetchNewsletters(1);
      nextTick(() => {
        applyAndDropSet([availableListsUlRef.value, selectedListsUlRef.value]);
      });
    });

    const createColumns = () => {
      return [
        {
          title: 'Subject',
          key: 'subject'
        },
        {
          title: 'Created',
          key: 'reg_date'
        }
      ]
    }

    const pagination = reactive({
      page: 1,
      pageSize: 10,
      onChange: (page) => {
        paginationReactive.page = page;
      },
      onUpdatePageSize: (pageSize) => {
        paginationReactive.pageSize = pageSize;
        paginationReactive.page = 1;
      }
    });

    return {
      store,
      ownStore,
      state,
      sendNewsletter,
      saveNewsletter,
      editContent,
      setSubject,
      availableListsUlRef,
      selectedListsUlRef,
      NSwitch,
      columns: createColumns(),
      pagination,
      railStyle: ({
                    focused,
                    checked
                  }) => {
        const style = {};
        if (checked) {
          style.background = "#06792a";
          if (focused) {
            style.boxShadow = "0 0 0 2px #d0305040";
          }
        }
        style.marginRight = "20px";
        return style;
      }
    };
  },
});
</script>

<style>
.send-actions-container button.btn {
  margin-right: 5px;
}

</style>
