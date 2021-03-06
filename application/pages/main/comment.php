<?php
namespace Page\Main;

use Aqua\Content\ContentType;
use Aqua\Core\App;
use Aqua\Log\ErrorLog;
use Aqua\Site\Page;
use Aqua\UI\Form;
use Aqua\UI\Template;

class Comment
extends Page
{
	public function reply_action($cType = null, $contentId = null, $commentId = null)
	{
		try {
			if(!$cType || !$contentId ||
			   !($cType = ContentType::getContentType($cType, 'key')) ||
			   !$cType->hasFilter('CommentFilter') ||
			   !($content = $cType->get($contentId)) ||
			   ($commentId && !($comment = $cType->getComment($commentId)))) {
				$this->error(404);

				return;
			}
			if($cType->hasFilter('ArchiveFilter') && $content->isArchived()) {
				$this->error(403);

				return;
			}
			if(empty($comment) && $this->request->method !== 'POST')  {
				$this->error(405);

				return;
			}
			$frm = new Form($this->request);
			$frm->textarea('content')
				->required();
			if($content->meta->get('comment-anonymously')) {
				$frm->checkbox('anonymous')
					->value(array( '1' => '' ))
					->setLabel(__('comment', 'comment-anonymously'));
			}
			$frm->submit();
			$frm->validate();
			if($frm->status !== Form::VALIDATION_SUCCESS) {
				if(empty($comment)) {
					$title = __('comment', 'comment');
				} else {
					$title = __('comment', 'reply-comment');
				}
				$this->title = $this->theme->head->section = $title;
				$this->theme->set('return', $cType->url(array( 'path' => array( $comment->content()->slug ) )));
				$tpl = new Template;
				$tpl->set('content', $content)
					->set('comment', isset($comment) ? $comment : null)
					->set('form', $frm)
					->set('page', $this);
				echo $tpl->render('content/comment-reply');
				return;
			}
			$this->response->redirect(302);
			try {
				if(($url = base64_decode($this->request->uri->getString('return', ''))) &&
				   parse_url($url, PHP_URL_HOST) === \Aqua\DOMAIN) {
					$this->response->redirect($url);
				} else {
					$this->response->redirect($cType->url(array(
						'path' => array( $content->slug ),
					    'hash' => 'comments'
					)));
				}
				$arguments = array(
					App::user()->account,
					$this->request->getString('content'),
					$content->meta->get('comment-anonymously') && $this->request->getInt('anonymous'),
					0,
					\Aqua\Content\Filter\CommentFilter\Comment::STATUS_PUBLISHED
				);
				if(isset($comment)) {
					$arguments[] = $comment;
				}
				$comment = call_user_func_array(array( $content, 'addComment' ), $arguments);
				if(!$comment) {
					return;
				} else if($comment->status === \Aqua\Content\Filter\CommentFilter\Comment::STATUS_FLAGGED) {
					App::user()->addFlash('warning', null, __('comment', 'flagged'));
				} else {
					App::user()->addFlash('success', null, __('comment', 'saved'));
				}
			} catch(\Exception $exception) {
				ErrorLog::logSql($exception);
				App::user()->addFlash('error', null, __('application', 'unexpected-error'));
			}
		} catch(\Exception $exception) {
			ErrorLog::logSql($exception);
			$this->error(500, __('application', 'unexpected-error-title'), __('application', 'unexpected-error'));
		}
	}

	public function report_action($cType = null, $commentId = null)
	{
		try {
			if(!$commentId || !$cType ||
			   !($cType = ContentType::getContentType($cType, 'key')) ||
			   !($comment = $cType->getComment($commentId))) {
				$this->error(404);

				return;
			}
			if($cType->hasFilter('ArchiveFilter') && $comment->content()->isArchived()) {
				$this->error(403);

				return;
			}
			$frm = new Form($this->request);
			$frm->textarea('report');
			$frm->submit();
			$frm->validate();
			if($frm->status !== Form::VALIDATION_SUCCESS) {
				$this->title = $this->theme->head->section = __('comment', 'report-comment');
				$this->theme->set('return', $cType->url(array( 'path' => array( $comment->content()->slug ) )));
				$tpl = new Template;
				$tpl->set('comment', isset($comment) ? $comment : null)
				    ->set('form', $frm)
				    ->set('page', $this);
				echo $tpl->render('content/comment-report');

				return;
			}
			$this->response->redirect(302);
			try {
				if(($url = base64_decode($this->request->uri->getString('return', ''))) &&
				   parse_url($url, PHP_URL_HOST) === \Aqua\DOMAIN) {
					$this->response->redirect($url);
				} else {
					$this->response->redirect($cType->url(array(
						'path' => array( $comment->content()->slug ),
						'hash' => 'comments'
					)));
				}
				if($cType->reportComment($comment, App::user()->account, $this->request->getString('report', ''))) {
					App::user()->addFlash('success', null, __('comment', 'report-sent'));
				}
			} catch(\Exception $exception) {
				ErrorLog::logSql($exception);
				App::user()->addFlash('error', null, __('application', 'unexpected-error'));
			}
		} catch(\Exception $exception) {
			ErrorLog::logSql($exception);
			$this->error(500, __('application', 'unexpected-error-title'), __('application', 'unexpected-error'));
		}
	}

	public function edit_action($cType = null, $commentId = null)
	{
		try {
			if(!$commentId || !$cType ||
			   !($cType = ContentType::getContentType($cType, 'key')) ||
			   !$cType->hasFilter('CommentFilter') ||
			   !($comment = $cType->getComment($commentId))) {
				$this->error(404);

				return;
			}
			if($comment->authorId !== App::user()->account->id ||
			   !$cType->filter('CommentFilter')->getOption('editing', true) ||
			   ($cType->hasFilter('ArchiveFilter') && $comment->content()->isArchived()) ||
			   $comment->status !== \Aqua\Content\Filter\CommentFilter\Comment::STATUS_PUBLISHED) {
				$this->error(403);

				return;
			}
			$frm = new Form($this->request);
			$content = $frm->textarea('content', true)->required();
			if(!$this->request->getString('content')) {
				$content->append($comment->bbCode);
			}
			$frm->submit();
			$frm->validate();
			if($frm->status !== Form::VALIDATION_SUCCESS) {
				$this->title = $this->theme->head->section = __('comment', 'edit-comment');
				$this->theme->set('return', $cType->url(array( 'path' => array( $comment->content()->slug ) )));
				$tpl = new Template;
				$tpl->set('comment', $comment)
				    ->set('form', $frm)
				    ->set('page', $this);
				echo $tpl->render('content/comment-edit');

				return;
			}
			$this->response->redirect(302);
			try {
				if(($url = base64_decode($this->request->uri->getString('return', ''))) &&
				   parse_url($url, PHP_URL_HOST) === \Aqua\DOMAIN) {
					$this->response->redirect($url);
				} else {
					$this->response->redirect($cType->url(array(
						'path'  => array( $comment->content()->slug ),
						'hash'  => 'comments',
					    'query' => array( 'root' => $comment->id )
					)));
				}
				if($comment->update(array(
					'bbcode_content' => $this->request->getString('content'),
				    'last_editor'    => App::user()->account->id
				))) {
					App::user()->addFlash('success', null, __('comment', 'comment-updated'));
				}
			} catch(\Exception $exception) {
				ErrorLog::logSql($exception);
				App::user()->addFlash('error', null, __('application', 'unexpected-error'));
			}
		} catch(\Exception $exception) {
			ErrorLog::logSql($exception);
			$this->error(500, __('application', 'unexpected-error-title'), __('application', 'unexpected-error'));
		}
	}
}
