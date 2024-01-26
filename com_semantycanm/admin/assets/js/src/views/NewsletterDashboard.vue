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
        <div class="form-group">
          <label for="testEmails">{{ store.translations.TEST_ADDRESS }}</label>
          <input type="text" class="form-control" id="testEmails" name="testEmails">
        </div>
        <div class="form-group">
          <div class="input-group mb-3">
            <input type="text"
                   class="form-control"
                   id="subject"
                   name="subject"
                   required
                   placeholder="Subject"
                   aria-label="Subject"
                   v-model="state.subject"
            >
            <button class="btn btn-outline-secondary"
                    type="button"
                    id="addSubjectBtn"
                    @click="setSubject"
                    style="margin: 5px;">{{ store.translations.FETCH_SUBJECT }}
            </button>
          </div>
        </div>
        <div class="form-group">
          <label for="messageContent">{{ store.translations.MESSAGE_CONTENT }}</label>
          <textarea class="form-control"
                    id="messageContent"
                    name="messageContent"
                    rows="10"
                    required
                    readonly></textarea>
        </div>
        <div class="send-actions-container">
          <button type="button"
                  class="btn btn-secondary"
                  @click="sendNewsletter">{{ store.translations.SEND_NEWSLETTER }}
          </button>

          <n-switch :round="false" :rail-style="railStyle">
            <template #checked>
              Activated
            </template>
            <template #unchecked>
              Test
            </template>
          </n-switch>

          <button type="button"
                  class="btn btn-secondary"
                  id="saveNewsletterBtn"
                  @click="saveNewsletter">{{ store.translations.SAVE_NEWSLETTER }}
          </button>
          <button type="button"
                  class="btn btn-secondary"
                  id="toggleEditBtn"
                  @click="editContent">{{ store.translations.EDIT }}
          </button>
        </div>
      </div>
    </div>
    <div class="row justify-content-center mt-5">
      <div class="col-md-12">
        <div class="header-container d-flex justify-content-between align-items-center">
          <h3 class="mb-4">{{ store.translations.NEWSLETTERS_LIST }}</h3>
          <div id="newsletterSpinner" class="spinner">
            <img src="components/com_semantycanm/assets/images/spinner.svg" alt="Loading" class="spinner-icon">
          </div>
          <div style="display: flex; justify-content: space-between; align-items: start;">
            <div style="color: gray; display: flex; gap: 5px; align-items: center;">
              <label for="totalNewsletterList">Total:</label><input type="text" id="totalNewsletterList"
                                                                    value="0" readonly
                                                                    style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
              <label for="currentNewsletterList">Page:</label><input type="text" id="currentNewsletterList"
                                                                     value="1" readonly
                                                                     style="width: 20px; border: none; background-color: transparent; color: inherit;"/>
              <label for="maxNewsletterList">of</label><input type="text" id="maxNewsletterList" value="1"
                                                              readonly
                                                              style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
            </div>
            <div class="pagination-container mb-3 me-2">
              <a class="btn btn-primary btn-sm" href="#"
                 id="firstPageNewsletterList">{{ store.translations.FIRST }}</a>
              <a class="btn btn-primary btn-sm" href="#"
                 id="previousPageNewsletterList">{{ store.translations.PREVIOUS }}</a>
              <a class="btn btn-primary btn-sm" href="#"
                 id="nextPageNewsletterList">{{ store.translations.NEXT }}</a>
              <a class="btn btn-primary btn-sm" href="#"
                 id="lastPageNewsletterList">{{ store.translations.LAST }}</a>
            </div>
          </div>

        </div>
        <div class="table-responsive" style="height: 200px;">
          <table class="table table-fixed">
            <thead>

            <tr>
              <th class="col-1">
                <button class="btn btn-outline-secondary refresh-button"
                        type="button"
                        id="refreshNewsLettersButton"
                        @click="store.fetchNewsletters(1)">
                  <img src="components/com_semantycanm/assets/images/refresh.png" alt="Refresh" class="refresh-icon">
                </button>
              </th>
              <th>{{ store.translations.SUBJECT }}</th>
              <th>{{ store.translations.REGISTERED }}</th>
            </tr>
            </thead>
            <tbody id="newsLettersList">
            <tr v-for="entry in store.newsLetterDocsView.docs" :key="entry.id">
              <td style="width: 5%;"><input type="checkbox" :value="entry.id"></td>
              <td style="width: 15%;">{{ entry.subject }}</td>
              <td style="width: 10%;">{{ entry.reg_date }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import {defineComponent, nextTick, onMounted, reactive, ref} from 'vue';
import {useGlobalStore} from "../stores/globalStore";
import {NSwitch} from "naive-ui";

export default defineComponent({
  name: 'NewsletterComponent',
  components: {
    NSwitch,
  },

  setup() {
    const availableListsUlRef = ref(null);
    const selectedListsUlRef = ref(null);
    const store = useGlobalStore();
    const newsletterRequest = new NewsletterRequest();
    const state = reactive({
      currentNewsletterId: '',
      testEmails: '',
      subject: '',
      messageContent: '',
      isTest: false
    });

    const validateNewsletterInputs = () => {
      const msgContent = document.getElementById('messageContent').value.trim();
      const subj = document.getElementById('subject').value.trim();

      if (msgContent === '') {
        showWarnBar("Message content is empty. It cannot be saved");
        return false;
      }
      if (subj === '') {
        showWarnBar("Subject cannot be empty");
        return false;
      }
      state.messageContent = msgContent;
      state.subject = subj;
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
      store.fetchNewsletters(1);
      nextTick(() => {
        applyAndDropSet([availableListsUlRef.value, selectedListsUlRef.value]);
      });
    });


    return {
      store,
      state,
      sendNewsletter,
      saveNewsletter,
      editContent,
      setSubject,
      availableListsUlRef,
      selectedListsUlRef,
      NSwitch,
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
        } else {
          style.background = "#2080f0";
          if (focused) {
            style.boxShadow = "0 0 0 2px #2080f040";
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
