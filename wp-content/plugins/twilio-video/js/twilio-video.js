var conversationsClient;
var activeConversation;
var previewMedia;

// check for WebRTC
if (!navigator.webkitGetUserMedia && !navigator.mozGetUserMedia) {
  alert('WebRTC is not available in your browser.');
}

jQuery(function() {

	jQuery.ajax({ url: "/wp-content/plugins/twilio-video/twilio-video.html" }).done(function( content ) {
		jQuery(content).appendTo('body')

		document.getElementById('enter-queue').onclick = function () {
			jQuery('#agent-prompt').toggle();
			jQuery('#wait-interstitial').toggle();
		};
		var qstoken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImN0eSI6InR3aWxpby1mcGE7dj0xIn0.eyJqdGkiOiJTS2VkZGNlNzM5MDI0MTAzMTVjMDQyYzc2M2MyNWRjMDRiLTE1ODQzNzgwNzIiLCJpc3MiOiJTS2VkZGNlNzM5MDI0MTAzMTVjMDQyYzc2M2MyNWRjMDRiIiwic3ViIjoiQUNjZTNhNTM3MjVlZmM4ZmQ3ODAxZDEzNzYzOTlmNDJkMyIsImV4cCI6MTU4NDM4MTY3MiwiZ3JhbnRzIjp7ImlkZW50aXR5IjoicHJvIiwidmlkZW8iOnsicm9vbSI6InBybyBjaGF0IHJvb20ifX19.xHwnUwKom4TWUCxCjqmkgeoM-RsE5UV6hdug2a1ZiqM';
		$.ajax
({
  type: "GET",
  url: "https://video.twilio.com/v1/Rooms",
  dataType: 'json',
  headers: {
    "Authorization": "Basic " + qstoken
  },
  success: function (data){
    console.log(data);
  }
});
		


// Successfully connected!
	function clientConnected() {
    //document.getElementById('invite-controls').style.display = 'block';
    console.log("Connected to Twilio. Listening for incoming Invites as '" + conversationsClient.identity + "'");

    conversationsClient.on('invite', function (invite) {
        console.log('Incoming invite from: ' + invite.from);
        invite.accept().then(conversationStarted);
    });
  }
  
  // Conversation is live
	function conversationStarted(conversation) {

		jQuery('#wait-interstitial').toggle();
		jQuery('#conversation-view').toggle();
	
    console.log('In an active Conversation');
    activeConversation = conversation;
    // Draw local video, if not already previewing
    if (!previewMedia) {
        conversation.localMedia.attach('#local-media');
    }

    // When a participant joins, draw their video on screen
    conversation.on('participantConnected', function (participant) {
        console.log("Participant '" + participant.identity + "' connected");
        participant.media.attach('#remote-media');
    });

    // When a participant disconnects, note in log
    conversation.on('participantDisconnected', function (participant) {
        console.log("Participant '" + participant.identity + "' disconnected");
    });

    // When the conversation ends, stop capturing local video
    conversation.on('ended', function (conversation) {
        console.log("Connected to Twilio. Listening for incoming Invites as '" + conversationsClient.identity + "'");
        conversation.localMedia.stop();
        conversation.disconnect();
        activeConversation = null;
    });
	};
});
