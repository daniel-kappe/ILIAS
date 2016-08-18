
var Conversation = function Conversation(id, participants)
{

	/**
	 * @type {string}
	 * @private
	 */
	var _id = id;

	/**
	 * @type {Array}
	 * @private
	 */
	var _participants = participants ? participants : [];

	/**
	 * @type {boolean}
	 * @private
	 */
	var _group = false;

	var _latestMessage = null;

	/**
	 * Returns the ID of the conversation;
	 *
	 * @returns {*}
	 */
	this.getId = function() {
		return _id;
	};

	this.matchesParticipants = function(participants) {
		for(var index in _participants)
		{
			if(_participants.hasOwnProperty(index) && !hasParticipant(_participants[index], participants))
			{
				return false;
			}
		}
		return true;
	};

	this.send = function(message) {

		forParticipants(function(participant){
			participant.send(message);
		});
	};

	this.emit = function(event, data) {
		forParticipants(function(participant){
			participant.emit(event, data);
		});
	};

	this.addParticipant = function(participant) {
		if(!hasParticipant(participant, _participants))
		{
			_participants.push(participant);
			participant.addConversation(this);
		}
	};

	this.getParticipants = function() {
		return _participants;
	};

	this.isGroup = function() {
		return _group;
	};

	this.setIsGroup = function(isGroup) {
		_group = isGroup;
	};

	this.setLatestMessage = function(message) {
		_latestMessage = message;
	};

	this.json = function() {
		var participants = [];

		for(var key in _participants) {
			participants.push(_participants[key].json());
		}

		return {
			id: _id,
			participants: participants,
			latestMessage: _latestMessage
		}
	};

	var forParticipants = function(callback) {
		for(var key in _participants) {
			if(_participants.hasOwnProperty(key)) {
				callback(_participants[key]);
			}
		}
	};

	var hasParticipant = function(participant, participants) {
		for(var key in participants) {
			if(participants.hasOwnProperty(key)) {
				var id = participants[key].id;

				if(typeof participants[key].getId == 'function'){
					id = participants[key].getId();
				}

				if(id == participant.getId()) {
					return true;
				}
			}

		}
		return false;
	}
};

module.exports = Conversation;