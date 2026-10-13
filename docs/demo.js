"use strict";
// UniFind public portfolio demo: all records are fictional and browser-local.
// This preview does not connect to the PHP/MySQL application.
const STORAGE_KEY = "unifind_portfolio_demo_v1";
const IMAGE_ROOT = "https://raw.githubusercontent.com/leen686/Unifind/main/images/";
const seed = [
  { id:"sample-wallet", itemName:"Black Wallet", category:"Wallet", date:"2026-03-21", location:"Library", description:"Found near the library study area.", image:"wallet.jpeg", owner:false },
  { id:"sample-keys", itemName:"Car Keys", category:"Keys", date:"2026-03-18", location:"Parking Area", description:"Set of silver keys.", image:"car key.jpeg", owner:false },
  { id:"sample-bag", itemName:"Backpack", category:"Bag", date:"2026-03-17", location:"Building 5", description:"Black backpack left in the hallway near classroom 204.", image:"backpack.webp", owner:false },
  { id:"sample-airpods", itemName:"AirPods Case", category:"Other", date:"2026-03-16", location:"Cafeteria", description:"White AirPods case found near the cafeteria.", image:"airpods.webp", owner:false }
];
const id = value => document.getElementById(value);
const escapeText = value => String(value ?? "").replace(/[&<>"']/g, char => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[char]));
function defaultState(){ return { custom:[], saved:[] }; }
function loadState(){
  try {
    const value = JSON.parse(localStorage.getItem(STORAGE_KEY));
    if (value && Array.isArray(value.custom) && Array.isArray(value.saved)) {
      return { custom:value.custom.filter(r=>r && typeof r.id==="string" && r.owner===true).slice(0,100),
               saved:value.saved.filter(s=>typeof s==="string").slice(0,100) };
    }
  } catch (_) {}
  return defaultState();
}
let state=loadState();
let editId=null;
function persist(){ try {localStorage.setItem(STORAGE_KEY,JSON.stringify(state));} catch (_) {showToast("Browser storage is unavailable; changes may not persist.");} }
function allReports(){return [...seed,...state.custom];}
function getReport(reportId){return allReports().find(r=>r.id===reportId);}
function imageSource(r){return r.owner ? null : IMAGE_ROOT + encodeURIComponent(r.image);}
function showToast(message){const t=id("toast"); t.textContent=message; window.clearTimeout(showToast.timer); showToast.timer=window.setTimeout(()=>{t.textContent="";},3000);}
function empty(message,action=false){return '<div class="empty"><h3>No reports here yet</h3><p>'+escapeText(message)+'</p>'+(action?'<a href="#add">Try adding a report →</a>':'')+'</div>';}
function card(r){
  const saved=state.saved.includes(r.id);
  const source=imageSource(r);
  return '<article class="report-card">'+
  (source?'<img loading="lazy" src="'+escapeText(source)+'" alt="'+escapeText(r.itemName)+'">':'<div class="empty" style="padding:48px 12px">Your demo report</div>')+
  '<h3>'+escapeText(r.itemName)+'</h3>'+
  '<p class="meta"><b>Category:</b> '+escapeText(r.category)+'</p>'+
  '<p class="meta"><b>Date:</b> '+escapeText(r.date)+'</p>'+
  '<p class="meta"><b>Location:</b> '+escapeText(r.location)+'</p>'+
  '<p class="description">'+escapeText(r.description)+'</p>'+
  '<div class="card-actions"><button class="btn primary" data-action="view" data-id="'+escapeText(r.id)+'">View</button>'+
  '<button class="btn secondary" data-action="save" data-id="'+escapeText(r.id)+'">'+(saved?'Unsave':'♡ Save')+'</button>'+
  (r.owner?'<button class="btn secondary" data-action="edit" data-id="'+escapeText(r.id)+'">Edit</button><button class="btn secondary danger" data-action="delete" data-id="'+escapeText(r.id)+'">Delete</button>':'')+
  '</div></article>';
}
function showReports(){
  const q=id("q").value.trim().toLowerCase(),cat=id("cat").value,sort=id("sort").value;
  let reports=allReports().filter(r=> (cat==="all"||r.category===cat) && [r.itemName,r.category,r.description,r.location].some(value=>String(value).toLowerCase().includes(q)));
  reports.sort((a,b)=>sort==="oldest"?a.date.localeCompare(b.date):b.date.localeCompare(a.date));
  id("report-list").innerHTML=reports.length?reports.map(card).join(""):empty("Try another search or category.");
  id("result-count").textContent=reports.length+" "+(reports.length===1?"report":"reports")+" found";
}
function showSaved(){
  const reports=allReports().filter(r=>state.saved.includes(r.id));
  id("saved-list").innerHTML=reports.length?reports.map(card).join(""):empty("Open All Reports and save an item.");
  id("saved-count").textContent=String(reports.length);
}
function showMine(){
  id("mine-list").innerHTML=state.custom.length?state.custom.map(card).join(""):empty("Create a sample report to see it here.",true);
}
function refresh(){showReports();showSaved();showMine();}
function route(){
  const target=(location.hash||"#home").substring(1);
  const page=["home","reports","saved","mine","add"].includes(target)?target:"home";
  for(const screen of document.querySelectorAll(".screen")) screen.hidden=screen.id!==page;
  for(const link of document.querySelectorAll("[data-tab]")){link.classList.toggle("active",link.dataset.tab===page);if(link.dataset.tab===page)link.setAttribute("aria-current","page");else link.removeAttribute("aria-current");}
  refresh();
  window.scrollTo({top:0,behavior:"instant"});
}
function closeDetail(){const dialog=id("detail");if(dialog.open)dialog.close();}
function showDetail(r){
  const source=imageSource(r);
  id("detail-body").innerHTML=(source?'<img class="detail-image" src="'+escapeText(source)+'" alt="'+escapeText(r.itemName)+'">':'')+
    '<h2 class="detail-name">'+escapeText(r.itemName)+'</h2>'+
    '<p><b>Category:</b> '+escapeText(r.category)+'</p><p><b>Date:</b> '+escapeText(r.date)+'</p>'+
    '<p><b>Location:</b> '+escapeText(r.location)+'</p><p>'+escapeText(r.description)+'</p>'+
    '<p class="tip">Demo data only. No private contact details are shared.</p>';
  id("detail").showModal();
}
function openEdit(r){
  if(!r.owner)return;
  editId=r.id;
  const form=id("report-form");
  for(const field of ["itemName","category","date","location","description"])form.elements.namedItem(field).value=r[field];
  id("form-title").textContent="Edit Item Report";id("submit").textContent="Save Changes";
  location.hash="#add";
}
function resetForm(){editId=null;id("report-form").reset();id("form-title").textContent="Add Item Report";id("submit").textContent="Add Report";}
document.addEventListener("click",event=>{
  const button=event.target.closest("button[data-action]");
  if(!button)return;
  const r=getReport(button.dataset.id);if(!r)return;
  switch(button.dataset.action){
    case "view":showDetail(r);break;
    case "save":
      if(state.saved.includes(r.id))state.saved=state.saved.filter(x=>x!==r.id);
      else state.saved.push(r.id);
      persist();refresh();showToast(state.saved.includes(r.id)?"Report saved":"Report removed from saved");break;
    case "edit":openEdit(r);break;
    case "delete":
      if(r.owner && window.confirm("Delete this sample report?")){
        state.custom=state.custom.filter(x=>x.id!==r.id);state.saved=state.saved.filter(x=>x!==r.id);
        persist();refresh();showToast("Demo report deleted");
      }break;
  }
});
id("report-form").addEventListener("submit",event=>{
  event.preventDefault();
  const form=event.currentTarget;if(!form.reportValidity())return;
  const record={itemName:form.elements.namedItem("itemName").value.trim(),
    category:form.elements.namedItem("category").value,
    date:form.elements.namedItem("date").value,
    location:form.elements.namedItem("location").value.trim(),
    description:form.elements.namedItem("description").value.trim(),owner:true};
  if(!record.itemName||!record.category||!record.date||!record.location||!record.description)return;
  if(editId){
    const index=state.custom.findIndex(r=>r.id===editId);
    if(index>=0)state.custom[index]={...state.custom[index],...record};
    showToast("Demo report updated");
  } else {
    const uniqueId="custom-"+Date.now().toString(36)+"-"+Math.random().toString(36).slice(2,8);
    state.custom.push({...record,id:uniqueId});
    showToast("Sample report added");
  }
  persist();resetForm();refresh();location.hash="#mine";
});
for(const filterId of ["q","cat","sort"])id(filterId).addEventListener(filterId==="q"?"input":"change",showReports);
id("close-detail").addEventListener("click",closeDetail);
id("detail").addEventListener("click",event=>{if(event.target===id("detail"))closeDetail();});
id("reset").addEventListener("click",()=>{
  if(window.confirm("Reset the demo? This removes only the reports and bookmarks saved in your browser.")){
    state=defaultState();persist();resetForm();refresh();location.hash="#home";showToast("Demo reset");
  }
});
window.addEventListener("hashchange",()=>{if(location.hash!=="#add" && editId)resetForm();route();});
route();
