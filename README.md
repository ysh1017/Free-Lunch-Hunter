# 免費午餐資訊分享平台
Free Lunch Information Sharing Platform

## 動機 
身為對金錢敏感的大學生，透過聽補習班講座、學校講座省下午餐錢可以說是必備技能吧！我們想建立一個資訊共享平台，讓窮學生隨時能得知免費午餐的資訊，完善互助精神，當一個堂堂正正的乞丐超人。

## 網頁大綱 ER-model & Database schema（資料表欄位設計）

### Database schema:
![Untitled (1)](https://github.com/ysh1017/Free-Lunch-Hunter/assets/101330673/a8da4f9f-316b-4286-905f-b06682a0eb53)

### ER-model: 
![ER-model](https://github.com/ysh1017/Free-Lunch-Hunter/assets/101330673/26dc2e7a-1033-4784-858a-67f37b9af17f)

## 功能簡介與特色

### 主要功能：
1. 主要分為三個身分：管理員、一般用戶、premium用戶。
2. 管理員可以新增「免費午餐」活動，一般用戶與premium用戶可以看到這些活動，並決定要不要加到行事曆。免費午餐活動會清楚顯示活動時間、地點、照片、報名網址、午餐內容等重要資訊。當免費午餐活動過期以後，將不會顯示在活動列表，以便使用者清晰查找未來活動。
3. 文章(貼文)功能：屬於社群互動功能。用戶可以在文章發表他們對免費午餐的想法，網友們可以透過留言、按讚、倒讚進行互動。premium用戶可以自由選擇針對發布時間新/舊、甚至按讚數進行排序。
4. 為了促進社群交流，透過發布文章、留言、按讚/倒讚(也可以儲值)，一般用戶可以累積點數，晉升為premium用戶，相較一般用戶只能瀏覽5篇文章、加入三個免費午餐活動，premium用戶擁有無上限的數量權限，更擁有尊爵的歡迎語、一鍵到最上方/最下方等功能。
5. 編輯與刪除功能：不管是用戶建立的文章、管理員建立的活動，皆可以進到「管理」頁面編輯、刪除；而用戶加入行事曆的免費午餐活動，也可以透過按鈕或行事曆刪除。
6. 行事曆可以分為month、week、day等檢視模式，也有回到today功能，讓用戶清楚知道自己加入的免費午餐活動有哪些，好好的安排行程。

### 其他功能：
- 預覽文章：使用者可以同步預覽文章顯示的效果，包含emoji能否正常顯示。
- RWD響應式布局：網頁在safari等行動裝置瀏覽器也能正常運作。

## 瓶頸與待解決之問題
1. 目前行事曆點下去會詢問要不要刪除活動。我們希望在未來可以展開顯示活動資訊(如地點、午餐內容等等)。
2. 目前重新排序會刷新頁面，我們希望未來能做到刷新頁面後，頁面進度自動回到剛剛的瀏覽位置。
3. 希望未來行事曆可以有「私人行程」，並提供根據重要度顯示不同顏色的功能。
4. 希望行事曆可以串接gmail，直接辨識活動標題、地點、時間，自動填入活動內容(這是個遠大的目標)。

## 綜合討論（過程中遇到的挑戰、解決方法、發掘出的新理解等）
引入網頁前端行事曆(FullCalendar)可能是我們遇到最困難的挑戰之一。我們透過閱讀文檔、網路教學影片(日本人教學影片)，學習如何將活動加入行事曆，並正確顯示活動資訊。

另外，貼文與文章排序功能也讓我們困擾許久，最後我們終於做出可以依照時間、按讚數排序的功能。

值得一提的是，我們因為常常需要遠端協作，我們使用了 VS Code 的 live share 與 NGROK 服務(將本機的 IP 埠號，對應到一個隨機產生的 HTTPS 網址)，將自己的電腦變成一台小型伺服器，以讓大家都能修改同一份程式碼以及連接同一個網站和資料庫，這些實用的做法也讓我們的協作能力提升。

## 檔案架構
```
FINAL_PROJECT
├── about.php
├── admin_dashboard.php
├── ArticleClass.php
├── comments.php
├── config.php
├── create_activities.php
├── egcalendar.php
├── FullCalendar.js
├── index.php
├── load_activities.php
├── login.php
├── logout.php
├── manage_activity.php
├── manage_articles.php
├── manage_calendar.php
├── money.php
├── navbar.php
├── post_article.php
├── premium_check.php
├── premiumindex.php
├── rate.php
├── recharge.php
├── register.php
├── script.js
├── scriptopen.js
├── style_activity.css
├── style_post.css
├── style.css
├── styleopen.css
├── styles.css
├── styles1.css
├── styles2.css
├── upgrade.php
```

## 總結與心得
網頁撰寫真的不容易，許多功能交互作用常常變得很亂。我們覺得要寫出乾淨的code是最不容易的，往往程式碼檔名命名混亂，註解也寫得不夠清楚讓大家協作起來需要double check。

這次經驗讓我們未來在co-work會更了解寫code應有的共識，也會往「寫出乾淨的code」持續努力！
