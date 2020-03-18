//LISTING DETAILS 
const prodTitle = document.querySelector('#pro_title');
const description=document.querySelector('#description');
const excerpt = document.querySelector('#excerpt');

//query selectors
const content = document.querySelector('#content');
const dateToday = document.querySelector('#dateToday');
const tableForm = document.querySelector('#tableForm');
const tableForm2 = document.querySelector('#tableForm2');
const submitButton = document.querySelector('#wcfm_products_simple_submit_button');

let productID = document.querySelector('#prodID').value;


const session = document.getElementsByClassName('session');
const time = document.getElementsByClassName('time');
const prices = document.getElementsByClassName('price');
const myurl= 'https://gigant.com.ph/gigantApi/';

//array for get data from rest
let globalData = [];

//  array for sending data to rest
let passData=[];

let dataObj ={
  date :[],
  time :[],
  session :[],
  price :[]
} 



// console.log(currentUser)

// display current date and time
let todays = new Date();
let monthName = todays.getMonth()+1;
let dayName = todays.getDate();
let yearName = todays.getFullYear();
let hourName = todays.getHours();
let hourNameGMT = todays.getHours()-8;
let minsName = todays.getMinutes();
let secondssName = todays.getSeconds();
if(hourName <10){
  hourName = '0'+hourName;
}
if(hourNameGMT < 10){
  hourNameGMT = '0'+hourNameGMT;
}
if(minsName < 10){
  minsName = '0'+minsName;
}

let currentDate = yearName+"-"+monthName+"-"+dayName+" "+hourName+":"+minsName+":"+secondssName ;
let currentDateGMT = yearName+"-"+monthName+"-"+dayName+" "+hourNameGMT+":"+minsName+":"+secondssName ;

function getData(data){
  let i=0;
  data.forEach(e=>{
    globalData[i] = {
      "title":e.priority+" slot/s",
      "start":e.from_date+"T"+e.from_range,
      "id":e.ID,
      "backgroundColor":"green",
      "textColor":"white"
    }
    //  if(e.status=="publish"){
    //   globalData[i]['backgroundColor'] = "blue";
    //   globalData[i]['textColor'] = "white";
    //  }else if(e.status=="pending-confirmation"){
    //   globalData[i]['backgroundColor'] = "red";
    //   globalData[i]['textColor'] = "white";
    //  }else if(e.status=="confirmed"){
    //   globalData[i]['backgroundColor'] = "green";
    //   globalData[i]['textColor'] = "white";
    //  }
    i++;
  });
  // console.log(globalData);
  return globalData;
}


// manually input 24 hour 

// console.log(arr);

document.addEventListener('DOMContentLoaded', function(e) {
  e.preventDefault();
  fetch(myurl+'user/'+userID).then(res=>res.json()).then(res=>{
  let dd = getData(res);
  
  //calendar
  let today = new Date();
  let month = today.getMonth()+1;
  let day= today.getDate();
  let year = today.getFullYear();
  
  var calendarEl = document.getElementById('calendar');
//   console.log(globalData);
  var calendar = new FullCalendar.Calendar(calendarEl, {
    plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'list','bootstrap'],
    height: 'parent',
    header: {
      right: 'prev,next today',
      left: 'title',
      center: 'dayGridMonth,listWeek'
    },
    defaultView: 'dayGridMonth',
    themeSystem:'standard',
    validRange: {
      start: yearName+"-"+monthName+"-"+dayName,
      end: ''
    },
    navLinks: true, // can click day/week names to navigate views
    editable: false,
    eventLimit: true, // allow "more" link when too many events
    eventLimitText: "More", //sets the text for more events
    selectable:true,
    dayClick: function(info){
    },
    select: function(info) {
      let listDates = getDates(info.startStr,info.endStr);
      tableForm.hidden=false;
      tableForm2.hidden=true;
      
      let minusDay = info.endStr.split("-");
      let decDay=minusDay[2]-1;
      if(decDay <10){
        decDay = "0"+decDay;
      }
      let newEndDateStr = minusDay[0]+"-"+minusDay[1]+"-"+decDay;
      // console.log(newEndDateStr);
      
      // dateToday.innerHTML=info.startStr+" / "+minusDay;
      dateToday.innerHTML = calendar.formatRange(info.startStr, newEndDateStr, {
        month: 'long',
        year: 'numeric',
        day: 'numeric',
        separator: ' to '
      })
      
    },
    
    events:dd,
    eventClick: function(info) {
    //   alert('Event: ' + info.event.description);
    //   alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
    //   alert('View: ' + info.view.type);
  
      // change the border color just for fun
      info.el.style.borderColor = 'white';
    },
    eventMouseEnter:function(mouseEnterInfo){
      mouseEnterInfo.el.style.backgroundColor="blue";
      mouseEnterInfo.el.style.cursor="pointer";
     
    },
    eventMouseLeave:function(mouseEnterInfo){
      mouseEnterInfo.el.style.backgroundColor="green";
    }
  });
  
  calendar.render();
  
//   console.log(dd);
  

});

});


// remove element to table 
function removeSlot(id){
  // console.log(id);
  let newid = "tr"+id;
  let countRow = content.querySelectorAll('tr');
  console.log(countRow);
  console.log(countRow.length);
  if(countRow.length<2){
    alert('Leave a Slot');
  }else{
    
    const tr = document.getElementById(newid);
    tr.remove();
    
  }
  
}

function dateFormat(e){
  let arr =[];
  let string= e.split("/");
  
  let month = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  console.log(month[string[1]-1]);
  
  console.log(string);
  dateToday.innerHTML = month[string[1]-1]+" "+string[2]+" "+string[0];
  
}

const convertTime12to24 = (time12h) => {
  const [time, modifier] = time12h.split(' ');
  
  let [hours, minutes] = time.split(':');
  
  if (hours === '12') {
    hours = '00';
  }
  
  if (modifier === 'PM') {
    hours = parseInt(hours, 10) + 12;
  }
  if(hours < 10){
    hours = "0"+hours;
  }
  
  return hours+':'+minutes;
}

//insert each date to an array
function getDates(startDate, stopDate) {
  // var dateArray = [];
  var currentDate = moment(startDate);
  var endDate = moment(stopDate);
  while (currentDate < endDate) {
    dataObj.date.push( moment(currentDate).format('YYYY-MM-DD') );
    currentDate = moment(currentDate).add(1, 'days');
  }
  return dataObj.date;
// console.log(dataObj.date)
}



//insert to availabilities
function sendData(data){
  fetch(myurl+'availability',{
    method:"POST",
    body:JSON.stringify(data)
  }).then(res=>{
    console.log('success');
  }).catch(e=>{
    console.log(e);
  })

}

//insert to posts
// let data = {
//     userid:userID,
//     post_date:currentDate,
//     post_date_gmt:currentDateGMT,
//     content:"samplecontent",
//     title:"product tile",
//     excerpt:"",
//     post_status:"publish",
//     comment_status:"open",
//     ping_status:"closed",
//     post_password:"",
//     post_name:"",
//     to_ping:"",
//     pinged:"",
//     post_modified:currentDate,
//     post_modified_gmt:currentDateGMT,
//     post_content_filtered:"",
//     post_parent:"0",
//     guid:"https://gigant.com.ph/160-autosave-v1/",
//     menu_order:"0",
//     post_type:"revision",
//     post_mime_type:"",
//     comment_count:"0"
//   }
// const sendPost = async () => await fetch(url+'posts',{
//   method:"POST",
//   body:JSON.stringify([data])
// }).then(res=>res.json()).then(res=>{
//   return res[0].lastID;
// });

  //put all data to array
  submitButton.addEventListener('click',function(e){
    e.preventDefault();
    
    // let serviceID = new Promise(function(resolve,reject){
    //   resolve(sendPost());
    // }); 
    // serviceID.then(function(prodID) {
   
  
    for(let i=0;i<session.length;i++){
      dataObj.session.push(session[i].value);
    }
    for(let i=0;i<time.length;i++){
      
      dataObj.time.push(convertTime12to24(time[i].value));
    }
    for(let i=0;i<prices.length;i++){
      dataObj.price.push(prices[i].value);
    }
    console.log(dataObj);
    
    for(let i=0;i<dataObj.date.length;i++){
      for(let j=0;j<dataObj.session.length;j++){
        for(let k=0;k<dataObj.time.length;k++){
          for(let l=0;l<dataObj.price.length;l++){
            passData[i]={
              kind:"availability#product",
              kind_id:productID,
              event_id:'',
              title:'',
              range_type:"time",
              from_date:dataObj.date[i],
              to_date: dataObj.date[i],
              from_range:dataObj.time[k],
              to_range:dataObj.time[k],
              appointable:'yes',
              priority:dataObj.session[j],
              qty:dataObj.session[j],
              ordering:'0',
            //   price:dataObj.price[l],
              date_created:currentDate,
              date_modified:currentDate
             
            }
          }
        }
      }
    }
    // expected output: "foo"
 
    console.log(passData)
    sendData(passData);
    
  });
// });